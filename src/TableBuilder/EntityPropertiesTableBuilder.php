<?php

namespace Drush\dmt_structure_export\TableBuilder;

use Drush\dmt_structure_export\Utilities;

/**
 * EntityPropertiesTableBuilder class.
 */
class EntityPropertiesTableBuilder extends TableBuilder {

  /**
   * Entity information.
   *
   * @var array
   */
  protected $entity_info;

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header = [
      // Entity data.
      'entity' => dt('Entity type'),
      'entity_count' => dt('Entity count'),
      'bundle' => dt('Bundle'),
      'bundle_count' => dt('Bundle count'),
      // Property data.
      'property_id' => dt('Property ID'),
      'property_label' => dt('Property Label'),
      'property_type' => dt('Property type'),
      'property_translatable' => dt('Property translatable'),
      'property_required' => dt('Property required'),
      // Field data.
      'property_field' => dt('Is field?'),
      'property_field_type' => dt('Field Type'),
      'property_field_module' => dt('Field module'),
      'property_field_cardinality' => dt('Field cardinality'),
      // Property data.
      // This property was requested to be placed as the last column and be
      // renamed from "Property count" to "Count of Populated fields".
      'property_count' => dt('Count of Populated fields'),
    ];

    $this->setHeader($header);
  }

  /**
   * {@inheritdoc}
   */
  public function buildRows() {
    $rows = $this->buildEntityRows() ?: [];
    $rows = $this->flattenRows($rows);

    $this->setRows($rows);
  }

  /**
   * Builds all entity rows.
   *
   * @return array
   *   The list of entity rows.
   */
  protected function buildEntityRows() {
    $row = [];
    $this->entity_info = entity_get_info();
    foreach ($this->entity_info as $entity_type => $et_info) {
      $row[$entity_type] = $this->buildEntityRow($entity_type);
    }

    return $row;
  }

  /**
   * Builds an entity row.
   *
   * @param $entity_type
   *   The entity type.
   *
   * @return array
   *   The entity row.
   */
  protected function buildEntityRow($entity_type) {
    $row = [];
    $row['entity'] = $this->entity_info[$entity_type]['label'] . ' (' . $entity_type . ')';
    $row['entity_count'] = Utilities::getEntityDataCount($entity_type);
    $row['bundles'] = $this->buildEntityBundleRows($entity_type);

    return $row;
  }

  /**
   * Builds all entity bundle rows.
   *
   * @param $entity_type
   *   The entity type.
   *
   * @return array
   *   The list of entity bundle rows.
   */
  protected function buildEntityBundleRows($entity_type) {
    $row = [];
    if (!empty($this->entity_info[$entity_type]['bundles'])) {
      foreach ($this->entity_info[$entity_type]['bundles'] as $bundle_id => $bundle_info) {
        $row[$bundle_id] = $this->buildEntityBundleRow($entity_type, $bundle_id);
      }
    }
    else {
      $row[$entity_type] = $this->buildEntityBundleRow($entity_type);
    }

    return $row;
  }

  /**
   * Builds an entity bundle row.
   *
   * @param $entity_type
   *   The entity type.
   *
   * @param null $bundle_id
   *   The entity's bundle id.
   *
   * @return array
   *   The entity bundle row.
   */
  protected function buildEntityBundleRow($entity_type, $bundle_id = NULL) {
    $row = [];

    if (!empty($bundle_id)) {
      $bundle_info = $this->entity_info[$entity_type]['bundles'][$bundle_id];
      $row['bundle'] = $bundle_info['label'] . ' (' . $bundle_id . ')';
      $row['bundle_count'] = Utilities::getEntityDataCount($entity_type, $bundle_id);
    }
    $row['bundle_properties'] = $this->buildEntityBundlePropertyRows($entity_type, $bundle_id);

    return $row;
  }

  /**
   * Builds all entity bundle properties rows.
   *
   * @param $entity_type
   *   The entity type.
   *
   * @param null $bundle_id
   *   The entities's bundle id.
   *
   * @return array
   *   The entity bundle property rows.
   */
  protected function buildEntityBundlePropertyRows($entity_type, $bundle_id = NULL) {
    $rows = [];

    $options = !empty($bundle_id) ? array('bundle' => $bundle_id) : array();
    $wrapper = entity_metadata_wrapper($entity_type, NULL, $options);
    $entity_properties = $wrapper->getPropertyInfo();
    foreach ($entity_properties as $property_id => $property_info) {
      // Skip read only properties.
      if (!empty($property_info['computed'])) {
        continue;
      }

      $property_row = [];
      $property_row['property_id'] = $property_id;
      $property_row['property_label'] = $property_info['label'];
      $property_row['property_type'] = $property_info['type'];
      $property_row['property_translatable'] = $property_info['translatable'] ? 'TRUE' : 'FALSE';
      $property_row['property_required'] = $property_info['required'] ? 'TRUE' : 'FALSE';

      // Field data.
      $property_row['property_field'] = !empty($property_info['field']) ? 'TRUE' : 'FALSE';
      if (!empty($property_info['field'])) {
        $field_base = field_info_field($property_id);
        $property_row['property_field_type'] = $field_base['type'];
        $property_row['property_field_module'] = $field_base['module'];
        $property_row['property_field_cardinality'] = ($field_base['cardinality'] == -1 ? 'UNLIMITED' : $field_base['cardinality']);

        foreach ($field_base['columns'] as $column_id => $column_info) {
          $property_column_row = $property_row;
          $property_column_row['property_id'] = $property_id . '/' . $column_id;
          $property_column_row['property_label'] = $property_info['label'] . ' / ' . $column_id;
          $property_column_row['property_type'] = $column_info['type'];
          $property_column_row['property_count'] = Utilities::getEntityPropertyDataCount($property_id, $column_id, $entity_type, $bundle_id);
          $rows[$property_id . '.' . $column_id] = $property_column_row;
        }
      }
      else {
        $rows[$property_id] = $property_row;
      }
    }

    return $rows;
  }

}
