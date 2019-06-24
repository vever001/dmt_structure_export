<?php

namespace Drush\dmt_structure_export;

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
   *   The result from the EntityFieldQuery count.
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
    return (int) $query->count()->execute();
  }

  /**
   * Returns the amount/count of values in DB for the given property/field.
   *
   * @param string $property_name
   *   The property/field name.
   * @param string $entity_type
   *   The entity type.
   * @param string $bundle
   *   (Optional) The entity bundle.
   *
   * @return int
   *   The result from the EntityFieldQuery count.
   */
  public static function getEntityPropertyDataCount($property_name, $entity_type, $bundle = NULL) {
    if ($entity_type === 'comment' && !empty($bundle)) {
      return 'Unavailable';
    }

    $field = field_info_field($property_name);
    $columns = array_keys($field['columns']);

    $query = new \EntityFieldQuery();
    $query->entityCondition('entity_type', $entity_type);

    if (!empty($bundle)) {
      $query->entityCondition('bundle', $bundle);
    }

    $query->fieldCondition($property_name, $columns[0], NULL, 'IS NOT');
    $query->addMetaData('account', user_load(1));
    return (int) $query->count()->execute();
  }

}
