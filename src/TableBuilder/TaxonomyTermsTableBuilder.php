<?php

namespace Drush\dmt_structure_export\TableBuilder;

/**
 * TaxonomyTermsTableBuilder class.
 */
class TaxonomyTermsTableBuilder extends TableBuilder {

  /**
   * Denotes that the entity is active.
   */
  const ENTITY_ACTIVE = 1;

  /**
   * Denotes that the entity is active.
   */
  const ENTITY_INACTIVE = 0;

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header = [
      'machine_name' => dt('Vocabulary'),
      'tid' => dt('Term ID'),
      'name' => dt('Term name'),
      'language' => dt('Term language'),
      'usage_published_entity' => dt('Usage (Published entity)'),
      'published_entity_types' => dt('Entity types (Published)'),
      'usage_unpublished_entity' => dt('Usage (Unpublished entity)'),
      'unpublished_entity_types' => dt('Entity types (Unpublished)'),
      'term_description' => dt('Term description'),
    ];
    $this->setHeader($header);
  }

  /**
   * {@inheritdoc}
   */
  public function buildRows() {
    // Build the query.
    $query = db_select('taxonomy_term_data', 'term', ['fetch' => \PDO::FETCH_ASSOC]);
    $query->innerJoin('taxonomy_vocabulary', 'voc', 'term.vid = voc.vid');
    $query->fields('voc', ['machine_name']);
    $query->fields('term', ['tid', 'name', 'language']);
    $query->addExpression('SUBSTRING(term.description, 1, 100)', 'term_description');
    $query->condition('term.language', ['und', 'en'], 'IN');
    $query->orderby('voc.machine_name');
    $query->orderby('term.vid');
    $query->orderby('term.name');
    // Execute the query.
    $rows = $query->execute()->fetchAllAssoc('tid', \PDO::FETCH_ASSOC);
    // Remove line breaks from all term description fields because they are not
    // handled correctly by the CSV parser and they break the CSV export.
    $this->sanitizeDescriptions($rows);
    // Append the term usage count for both published and unpublished entities.
    $this->appendTermUsageInfo($rows);
    $this->setRows($rows);
  }

  /**
   * Counts the term usage and appends it to the term row.
   *
   * @param $rows
   *   The rows of existing taxonomy terms.
   */
  protected function appendTermUsageInfo(&$rows) {
    $term_reference_fields = $this->getTermReferenceFields();
    foreach ($rows as &$row) {
      $usage_published_entity = $this->getTermUsage($row['tid'], $term_reference_fields, self::ENTITY_ACTIVE);
      $usage_unpublished_entity = $this->getTermUsage($row['tid'], $term_reference_fields, self::ENTITY_INACTIVE);
      $row['usage_published_entity'] = $usage_published_entity['count'];
      $row['published_entity_types'] = implode(', ', $usage_published_entity['entity_types']);
      $row['usage_unpublished_entity'] = $usage_unpublished_entity['count'];
      $row['unpublished_entity_types'] = implode(', ', $usage_unpublished_entity['entity_types']);
    }
    unset($row);
  }

  /**
   * Returns an array of active fields that reference taxonomy terms.
   *
   * Mind that a taxonomy term reference field could be used by different entity
   * types, eg. nodes, comments, users, files, etc.
   *
   * @return array
   *   The list of term reference fields.
   */
  protected function getTermReferenceFields() {
    // Declare the response array.
    $term_reference_fields = [];
    // Build the query.
    $query = db_select('field_config', 'fc', array('fetch' => \PDO::FETCH_ASSOC,));
    $query->distinct();
    $query->innerJoin('field_config_instance', 'fci', 'fc.id = fci.field_id');
    $query->groupBy('fc.id');
    $query->groupBy('fc.field_name');
    $query->groupBy('fci.entity_type');
    $query->fields('fc', ['id', 'field_name']);
    $query->fields('fci', ['entity_type']);
    $query->addField('fci', 'id', 'instance_id');
    $query->condition('fc.active', 1);
    $query->condition('fc.storage_active', 1);
    $query->condition('fc.deleted', 0);
    $query->condition('fci.deleted', 0);
    $query->condition('fc.type', 'taxonomy_term_reference');
    $query->orderby('fci.field_name', 'ASC');
    // Execute the query.
    $results = $query->execute();
    // Prepare the response array.
    foreach ($results as $record) {
      $field = [];
      $field['field_id'] = $record['id'];
      $field['field_instance_id'] = $record['instance_id'];
      $field['field_name'] = $record['field_name'];
      $field['entity_type'] = $record['entity_type'];
      $term_reference_fields[$field['field_name'] . ':' . $field['entity_type']] = $field;
    }

    // Return the response array.
    return $term_reference_fields;
  }

  /**
   * Returns the taxonomy term usage.
   *
   * Mind that a taxonomy term could be referenced by different entity types,
   * eg. "node", "comment", "user", "file", "field_collection_item", "bean" etc.
   *
   * @param $term_id
   *   The term id to search for.
   * @param $term_reference_fields
   *   The list of term reference fields.
   * @param $entity_status
   *   The status of the entity, ENTITY_ACTIVE or ENTITY_INACTIVE. The default
   *   value is ENTITY_ACTIVE.
   *
   * @return array
   *   The usage count and the entity type usage.
   */
  protected function getTermUsage($term_id, $term_reference_fields, $entity_status=self::ENTITY_ACTIVE) {
    $usage = [];
    $entity_types = [];
    $count = 0;
    foreach ($term_reference_fields as $term_reference_field) {
      $has_status_property = $this->entityHasProperty($term_reference_field['entity_type'], 'status');
      $query = new \EntityFieldQuery();
      $query->entityCondition('entity_type', $term_reference_field['entity_type']);
      $query->fieldCondition($term_reference_field['field_name'], 'tid', $term_id);
      // For example, "field_collection_item", "bean" and "taxonomy_term" do not
      // have a status property.
      if ($has_status_property) {
        $query->propertyCondition('status', $entity_status);
      }
      $query->addMetaData('account', user_load(1));
      $result = (!($has_status_property) && $entity_status === self::ENTITY_INACTIVE) ? 0 : (int) $query->count()->execute();
      if ($result > 0) {
        $count += $result;
        if (!in_array($term_reference_field['entity_type'], $entity_types)) {
          $entity_types[] = $term_reference_field['entity_type'];
        }
      }
    }
    $usage['count'] = $count;
    sort($entity_types);
    $usage['entity_types'] = $entity_types;

    return $usage;
  }

  /**
   * Searches an entity type for a given field.
   *
   * @param string $entity_type
   *   The entity type.
   * @param string $property
   *   The property to search for.
   *
   * @return bool
   *   The answer to whether the entity type has a given property or not.
   */
  protected function entityHasProperty($entity_type, $property) {
    $entity_info = entity_get_info($entity_type);
    $entity_fields = $entity_info['schema_fields_sql']['base table'];

    return (is_null($entity_fields) ? FALSE : (array_search($property, $entity_fields) ? TRUE : FALSE));
  }

  /**
   * Sanitizes all term descriptions.
   *
   * @param $rows
   *   The rows of taxonomy terms.
   */
  protected function sanitizeDescriptions(&$rows) {
    foreach ($rows as &$row) {
      $row['term_description'] = $this->sanitizeDescription(trim($row['term_description']));
    }
  }

  /**
   * Sanitizes a single term description.
   *
   * The CSV parser cannot handle correctly line breaks. This results to a
   * "broken" CSV file.
   *
   * @param $string
   *   The string to sanitize.
   *
   * @return string|string[]|null
   *   The sanitized string or null.
   */
  protected function sanitizeDescription($string) {
    $patterns = ['/"/', '/\r?\n/'];
    $replacements = ['""', '<br>'];

    return preg_replace($patterns, $replacements, $string);
  }

}
