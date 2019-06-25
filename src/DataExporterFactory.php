<?php

namespace Drush\dmt_structure_export;

/**
 * Class DataExporterFactory.
 */
class DataExporterFactory {

  /**
   * Returns the list of export types and associated classes.
   *
   * @return array
   *   The export types as associative array.
   */
  public static function getExportTypes() {
    return array(
      'entity_bundles' => 'EntityBundlesDataExporter',
      'entity_properties' => 'EntityPropertiesDataExporter',
      'fields' => 'FieldsDataExporter',
      'modules' => 'ModulesDataExporter',
      'taxonomy_terms' => 'TaxonomyTermsDataExporter',
    );
  }

  /**
   * Create a DataExporter class for the given export type.
   *
   * @param string $export_type
   *   The export type. See self::getExportTypes().
   *
   * @return \Drush\dmt_structure_export\DataExporter\DataExporter
   *   A DataExporter instance.
   *
   * @throws \Exception
   */
  public static function createInstance($export_type) {
    $export_types = self::getExportTypes();
    if (isset($export_types[$export_type])) {
      $class = 'Drush\\dmt_structure_export\\DataExporter\\' . $export_types[$export_type];
      return new $class();
    }
    else {
      throw new \Exception(sprintf('Unknown export type %s', array($export_type)));
    }
  }

}
