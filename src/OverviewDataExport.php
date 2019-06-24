<?php

namespace Drush\dmt_structure_export;

/**
 * OverviewDataExport class.
 */
class OverviewDataExport extends DataExport {

  /**
   * OverviewDataExport constructor.
   */
  public function __construct() {
    $this->header = array(
      'entity' => 'Entity type',
      'entity_count' => 'Entity count',
      'bundle' => 'Bundle',
      'bundle_count' => 'Bundle count',
    );
  }

  /**
   * Process and generate the rows.
   */
  public function process() {
    $entity_info = entity_get_info();

    foreach ($entity_info as $entity_type => $et_info) {
      $this->addRow(array(
        // Entity Label.
        'entity' => $et_info['label'] . ' (' . $entity_type . ')',
        // Entity count.
        'entity_count' => Utilities::getEntityDataCount($entity_type),
      ));

      if (!empty($et_info['bundles']) && count($et_info['bundles']) > 1) {
        foreach ($et_info['bundles'] as $bundle => $bundle_info) {
          $this->addRow(array(
            'bundle' => $bundle_info['label'] . ' (' . $bundle . ')',
            'bundle_count' => Utilities::getEntityDataCount($entity_type, $bundle),
          ));
        }
      }
    }
  }

}