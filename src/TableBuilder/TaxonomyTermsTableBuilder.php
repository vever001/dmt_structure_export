<?php

namespace Drush\dmt_structure_export\TableBuilder;

/**
 * TaxonomyTermsTableBuilder class.
 */
class TaxonomyTermsTableBuilder extends TableBuilder {

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
      'usage_unpublished_entity' => dt('Usage (Unpublished entity)'),
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
      $row['usage_published_entity'] = $this->getTermUsage($row['tid'], $term_reference_fields, NODE_PUBLISHED);
      $row['usage_unpublished_entity'] = $this->getTermUsage($row['tid'], $term_reference_fields, NODE_UNPUBLISHED);
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
    $query->fields('fc', ['id', 'field_name', 'data']);
    $query->fields('fci', ['entity_type']);
    $query->condition('fc.active', 1);
    $query->condition('fc.storage_active', 1);
    $query->condition('fc.deleted', 0);
    $query->condition('fci.deleted', 0);
    $query->condition('fc.type', 'taxonomy_term_reference');
    // Remove the following condition to get all entity types that use these
    // taxonomy term reference fields.
    $query->condition('fci.entity_type', 'node');
    $query->orderby('fci.field_name', 'ASC');
    // Execute the query.
    $results = $query->execute();
    // Prepare the response array.
    foreach ($results as $record) {
      $field = [];
      $field = unserialize($record['data']);
      $field['field_id'] = $record['id'];
      $field['field_name'] = $record['field_name'];
      $field['entity_type'] = $record['entity_type'];
      $term_reference_fields[$field['field_name']] = $field;
    }

    // Return the response array.
    return $term_reference_fields;
  }

  /**
   * Returns the taxonomy term usage.
   *
   * Mind that a taxonomy term could be referenced by different entity types,
   * eg. nodes, comments, users, files, etc.
   *
   * @param $term_id
   *   The term id to search for.
   * @param $term_reference_fields
   *   The list of term reference fields.
   * @param $entity_status
   *   The status of the entity, NODE_PUBLISHED or NODE_UNPUBLISHED.
   *
   * @return int
   *   The usage count.
   */
  protected function getTermUsage($term_id, $term_reference_fields, $entity_status=NODE_PUBLISHED) {
    $result = 0;
    foreach ($term_reference_fields as $term_reference_field) {
      $query = new \EntityFieldQuery();
      $query->entityCondition('entity_type', $term_reference_field['entity_type']);
      $query->fieldCondition($term_reference_field['field_name'], 'tid', $term_id);
      $query->propertyCondition('status', $entity_status);
      $query->addMetaData('account', user_load(1));
      $result += (int) $query->count()->execute();
    }

    return $result;
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
