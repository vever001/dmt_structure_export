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
    if ($entity_type === 'rdf_entity') {
      // Skip for rdf_entity (bug?).
      // TypeError: Return value of Drupal\rdf_entity\RdfGraphHandler::
      // getEntityTypeGraphUris() must be of the type array, null returned
      // in Drupal\rdf_entity\RdfGraphHandler->getEntityTypeGraphUris()
      // (line 164 of rdf_entity/src/RdfGraphHandler.php).
      return 'Unavailable';
    }

    $query = \Drupal::entityQuery($entity_type);
    if (!empty($bundle)) {
      $entity_type = \Drupal::entityTypeManager()->getDefinition($entity_type);
      if ($bundle_key = $entity_type->getKey('bundle')) {
        $query->condition($bundle_key, $bundle);
      }
    }

    $query->accessCheck(FALSE);
    return (int) $query->count()->execute();
  }

  /**
   * Returns the amount/count of values in DB for the given property/field.
   *
   * @param string $entity_type
   *   The entity type.
   * @param string $field
   *   The property/field name.
   * @param string $bundle
   *   (Optional) The entity bundle.
   *
   * @return int
   *   The result from the EntityFieldQuery count.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public static function getEntityPropertyDataCount($entity_type, $field, $bundle = NULL) {
    if ($entity_type === 'rdf_entity') {
      // Skip for rdf_entity (bug?).
      // TypeError: Return value of Drupal\rdf_entity\RdfGraphHandler::
      // getEntityTypeGraphUris() must be of the type array, null returned
      // in Drupal\rdf_entity\RdfGraphHandler->getEntityTypeGraphUris()
      // (line 164 of rdf_entity/src/RdfGraphHandler.php).
      return 'Unavailable';
    }

    $query = \Drupal::entityQuery($entity_type);

    if (!empty($bundle)) {
      $entity_type = \Drupal::entityTypeManager()
        ->getDefinition($entity_type);
      if ($bundle_key = $entity_type->getKey('bundle')) {
        $query->condition($bundle_key, $bundle);
      }
    }

    $query->condition($field, NULL, 'IS NOT');
    $query->accessCheck(FALSE);
    return (int) $query->count()->execute();
  }

}
