<?php

namespace Drush\dmt_structure_export\DataExporter;

use Drush\dmt_structure_export\Utilities;

/**
 * EntitiesDataExporter class.
 */
class EntitiesDataExporter extends DataExporter {

  /**
   * EntitiesDataExporter constructor.
   */
  public function __construct() {
    $this->header = array(
      // Entity data.
      'entity' => 'Entity type',
      'entity_count' => 'Entity count',
      'bundle' => 'Bundle',
      'bundle_count' => 'Bundle count',
      // Property data.
      'property_id' => 'Property ID',
      'property_label' => 'Property Label',
      'property_type' => 'Property type',
      'property_translatable' => 'Property translatable',
      'property_required' => 'Property required',
      'property_count' => 'Property count',
      // Field data.
      'property_field' => 'Is field?',
      'property_field_type' => 'Field Type',
      'property_field_module' => 'Field module',
      'property_field_cardinality' => 'Field cardinality',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function process() {
    $entity_info = entity_get_info();

    foreach ($entity_info as $entity_type => $et_info) {
      // Entity data.
      $row = array(
        'entity' => $et_info['label'] . ' (' . $entity_type . ')',
        'entity_count' => Utilities::getEntityDataCount($entity_type),
      );
      $this->addRow($row);

      if (!empty($et_info['bundles'])) {
        foreach ($et_info['bundles'] as $bundle => $bundle_info) {
          if (count($et_info['bundles']) > 1) {
            $row = array(
              'bundle' => $bundle_info['label'] . ' (' . $bundle . ')',
              'bundle_count' => Utilities::getEntityDataCount($entity_type, $bundle),
            );
            $this->addRow($row);
          }

          // Property data.
          $wrapper = entity_metadata_wrapper($entity_type, NULL, array('bundle' => $bundle));
          $entity_properties = $wrapper->getPropertyInfo();
          foreach ($entity_properties as $property_id => $property_info) {
            $this->processEntityProperty($property_id, $property_info, $entity_type, $bundle);
          }
        }
      }
    }
  }

  /**
   * Process a single entity property.
   */
  protected function processEntityProperty($property_id, $property_info, $entity_type, $bundle) {
    // Do not export read only properties.
    if (!empty($property_info['computed'])) {
      return;
    }

    $row = array(
      'property_id' => $property_id,
      'property_label' => $property_info['label'],
      'property_type' => $property_info['type'],
      'property_translatable' => $property_info['translatable'] ? 'YES' : 'NO',
      'property_required' => $property_info['required'] ? 'YES' : 'NO',
    );

    // Field data.
    $row['property_field'] = !empty($property_info['field']) ? 'YES' : 'NO';
    if (!empty($property_info['field'])) {
      $field_base = field_info_field($property_id);
      $row['property_field_type'] = $field_base['type'];
      $row['property_field_module'] = $field_base['module'];
      $row['property_field_cardinality'] = ($field_base['cardinality'] == -1 ? 'UNLIMITED' : $field_base['cardinality']);

      foreach ($field_base['columns'] as $column_id => $column_info) {
        // Add field column row.
        $this->addRow(array_merge($row, array(
          'property_id' => $property_id . '/' . $column_id,
          'property_field_type' => $column_info['type'],
          'property_count' => Utilities::getEntityPropertyDataCount($property_id, $column_id, $entity_type, $bundle),
        )));
      }
    }
    else {
      // Add property row.
      $this->addRow($row);
    }
  }

}
