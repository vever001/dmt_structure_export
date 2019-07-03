<?php

namespace Drush\dmt_structure_export\TableBuilder;

use Drush\dmt_structure_export\Utilities;

/**
 * EntityPropertiesTableBuilder class.
 */
class EntityPropertiesTableBuilder extends TableBuilder {

  /**
   * {@inheritdoc}
   */
  protected function buildHeader() {
    $this->header = [
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
      'property_count' => dt('Property count'),
      // Field data.
      'property_field' => dt('Is field?'),
      'property_field_type' => dt('Field Type'),
      'property_field_module' => dt('Field module'),
      'property_field_cardinality' => dt('Field cardinality'),
    ];
    return $this->header;
  }

  /**
   * {@inheritdoc}
   */
  public function buildRows() {
    $this->rows = [];
    $entity_info = entity_get_info();

    foreach ($entity_info as $entity_type => $et_info) {
      // Entity data.
      $entity_row = [
        'entity' => $et_info['label'] . ' (' . $entity_type . ')',
        'entity_count' => Utilities::getEntityDataCount($entity_type),
      ];
      $this->rows[] = $entity_row;

      if (!empty($et_info['bundles'])) {
        foreach ($et_info['bundles'] as $bundle => $bundle_info) {
          if (count($et_info['bundles']) > 1) {
            $bundle_row = [
              'bundle' => $bundle_info['label'] . ' (' . $bundle . ')',
              'bundle_count' => Utilities::getEntityDataCount($entity_type, $bundle),
            ];
            $this->rows[] = $bundle_row;
          }

          // Property data.
          $wrapper = entity_metadata_wrapper($entity_type, NULL, ['bundle' => $bundle]);
          $entity_properties = $wrapper->getPropertyInfo();
          foreach ($entity_properties as $property_id => $property_info) {
            $this->buildEntityPropertyRows($property_id, $property_info, $entity_type, $bundle);
          }
        }
      }
    }

    return $this->rows;
  }

  /**
   * Process a single entity property.
   */
  protected function buildEntityPropertyRows($property_id, $property_info, $entity_type, $bundle) {
    // Do not export read only properties.
    if (!empty($property_info['computed'])) {
      return;
    }

    $row = [
      'property_id' => $property_id,
      'property_label' => $property_info['label'],
      'property_type' => $property_info['type'],
      'property_translatable' => $property_info['translatable'] ? 'TRUE' : 'FALSE',
      'property_required' => $property_info['required'] ? 'TRUE' : 'FALSE',
    ];

    // Field data.
    $row['property_field'] = !empty($property_info['field']) ? 'TRUE' : 'FALSE';
    if (!empty($property_info['field'])) {
      $field_base = field_info_field($property_id);
      $row['property_field_type'] = $field_base['type'];
      $row['property_field_module'] = $field_base['module'];
      $row['property_field_cardinality'] = ($field_base['cardinality'] == -1 ? 'UNLIMITED' : $field_base['cardinality']);

      foreach ($field_base['columns'] as $column_id => $column_info) {
        // Add field column row.
        $this->rows[] = array_merge($row, [
          'property_id' => $property_id . '/' . $column_id,
          'property_label' => $property_info['label'] . ' / ' . $column_id,
          'property_type' => $column_info['type'],
          'property_count' => Utilities::getEntityPropertyDataCount($property_id, $column_id, $entity_type, $bundle),
        ]);
      }
    }
    else {
      // Add property row.
      $this->rows[] = $row;
    }
  }

}
