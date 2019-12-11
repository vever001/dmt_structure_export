<?php

namespace Drush\dmt_structure_export;

use Drush\Log\LogLevel;

/**
 * Class Utilities.
 *
 * @package Drush\dmt_structure_export
 */
class Utilities {

  /**
   * Returns the amount/count of entities in DB.
   *
   * @param string $entity_type
   *   The entity type.
   * @param string $bundle
   *   (Optional) The entity bundle.
   *
   * @return int
   *   The result from the EntityFieldQuery count, or -1 on failure.
   */
  public static function getEntityDataCount($entity_type, $bundle = NULL) {
    if ($entity_type === 'comment' && !empty($bundle)) {
      return 'Unavailable';
    }

    $query = new \EntityFieldQuery();
    $query->entityCondition('entity_type', $entity_type);

    if (!empty($bundle)) {
      $query->entityCondition('bundle', $bundle);
    }

    $query->addMetaData('account', user_load(1));
    try {
      return (int) $query->count()->execute();
    }
    catch (\Exception $e) {
      // Use dt() with "!" instead of "@" placeholders, to avoid html in cli
      // output.
      // @todo Is this the recommended way to do this?
      drush_log(
        dt(
          "Exception while counting !type entities:\n!message",
          [
            '!type' => ($bundle === NULL) ? $entity_type : "$entity_type:$bundle",
            '!message' => $e->getMessage(),
          ]),
        LogLevel::WARNING);
      return -1;
    }
  }

  /**
   * Returns the amount/count of values in DB for the given property/field.
   *
   * @param string $property_name
   *   The property/field name.
   * @param string $column
   *   The property/field column name.
   * @param string|array $entity_types
   *   (Optional) The entity type or an array of entity types.
   * @param string $bundles
   *   (Optional) The entity bundle or an array of entity bundles.
   *
   * @return int
   *   The result from the EntityFieldQuery count.
   */
  public static function getEntityPropertyDataCount($property_name, $column, $entity_types, $bundles = NULL) {
    if ($entity_types === 'comment' && !empty($bundles)) {
      return 'Unavailable';
    }

    $query = new \EntityFieldQuery();
    $query->entityCondition('entity_type', $entity_types, is_array($entity_types) ? 'IN' : '=');

    if (!empty($bundles)) {
      $entity_type = is_array($entity_types) ? current($entity_types) : $entity_types;
      $entity_info = entity_get_info($entity_type);
      if (count($entity_info['bundles']) > 1) {
        $query->entityCondition('bundle', $bundles, is_array($bundles) ? 'IN' : '=');
      }
    }

    $query->fieldCondition($property_name, $column, NULL, 'IS NOT');
    $query->addMetaData('account', user_load(1));
    return (int) $query->count()->execute();
  }

}
