<?php

namespace Drush\dmt_structure_export;

/**
 * Class TableBuilderManager.
 */
class TableBuilderManager {

  /**
   * Returns the list of TableBuilder types and associated classes.
   *
   * @return array
   *   The TableBuilder types as associative array.
   */
  public static function getTableBuilderTypes() {
    return [
      'entity_bundles' => 'EntityBundlesTableBuilder',
      'entity_properties' => 'EntityPropertiesTableBuilder',
      'fields' => 'FieldsTableBuilder',
      'modules' => 'ModulesTableBuilder',
      'taxonomy_terms' => 'TaxonomyTermsTableBuilder',
    ];
  }

  /**
   * Create an TableBuilder object for the given type.
   *
   * @param string $type
   *   The TableBuilder type. See self::getTableBuilderTypes().
   *
   * @return \Drush\dmt_structure_export\TableBuilder\TableBuilderInterface
   *   A TableBuilder instance.
   *
   * @throws \Exception
   */
  public static function createInstance($type) {
    $types = self::getTableBuilderTypes();
    if (isset($types[$type])) {
      $class = 'Drush\\dmt_structure_export\\TableBuilder\\' . $types[$type];
      return new $class();
    }
    else {
      throw new \Exception(sprintf('Unknown export type %s', [$type]));
    }
  }

}
