<?php

namespace Drush\dmt_structure_export\TableBuilder;

/**
 * ModulesTableBuilder class.
 */
class ModulesTableBuilder extends TableBuilder {

  /**
   * ModulesTableBuilder constructor.
   */
  public function __construct() {
    $this->header = [
      'package' => dt('Package'),
      'machine_name' => dt('Machine name'),
      'label' => dt('Label'),
      'type' => dt('Type'),
      'status' => dt('Status'),
      'core' => dt('Core version'),
      'version' => dt('Version'),
      'description' => dt('Description'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildRows() {
    $this->rows = [];

    $modules = \system_rebuild_module_data();
    // @TODO Inject this?
    $themes = \Drupal::service('theme_handler')->rebuildThemeData();
    $both = array_merge($modules, $themes);

    /** @var \Drupal\Core\Extension\Extension $extension */
    foreach ($both as $key => $extension) {
      $this->rows[$key] = [
        'package' => $extension->info['package'] ?? '',
        'machine_name' => $extension->getName(),
        'label' => $extension->info['name'],
        'type' => $extension->getType(),
        'status' => ucfirst($this->extensionStatus($extension)),
        'core' => $extension->info['core'] ?? '',
        'version' => $extension->info['version'],
        'description' => $extension->info['description'],
      ];
    }

    return $this->rows;
  }

  /**
   * Calculate an extension status based on current status and schema version.
   */
  public function extensionStatus($extension) {
    return $extension->status == 1 ? 'enabled' : 'disabled';
  }

}
