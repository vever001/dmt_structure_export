<?php

namespace Drush\dmt_structure_export\DataExporter;

use Drush\dmt_structure_export\Utilities;

/**
 * FieldsDataExporter class.
 */
class FieldsDataExporter extends DataExporter implements DataExporterInterface {

  /**
   * FieldsDataExporter constructor.
   */
  public function __construct() {
    $this->header = array(
      'field_id' => dt('Field ID'),
      'field_name' => dt('Field name'),
      'field_type' => dt('Field type'),
      'field_module' => dt('Field module'),
      'field_cardinality' => dt('Field cardinality'),
      'field_translatable' => dt('Translatable'),
      'field_count' => dt('Field count'),
      'field_used_in' => dt('Used in'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function process() {
    $fields = field_info_fields();
    foreach ($fields as $field_id => $field_info) {
      if (!empty($field_info['bundles'])) {
        $row = array(
          'field_id' => $field_info['id'],
          'field_name' => $field_info['field_name'],
          'field_type' => $field_info['type'],
          'field_module' => $field_info['module'],
          'field_cardinality' => ($field_info['cardinality'] == -1 ? 'UNLIMITED' : $field_info['cardinality']),
          'field_translatable' => $field_info['translatable'] ? 'YES' : 'NO',
        );

        $column = current(array_keys($field_info['columns']));
        $entity_types = array_keys($field_info['bundles']);
        $row['field_count'] = Utilities::getEntityPropertyDataCount($field_id, $column, $entity_types);

        $used_in_array = array();
        foreach ($field_info['bundles'] as $entity => $bundles) {
          $used_in_array[] = dt('@entity (@bundles)', [
            '@entity' => $entity,
            '@bundles' => implode(', ', $bundles),
          ]);
        }
        $row['field_used_in'] = implode(', ', $used_in_array);

        $this->addRow($row);
      }
    }
  }

}
