<?php

namespace Drush\dmt_structure_export\DataExporter;

/**
 * ModulesDataExporter class.
 */
class ModulesDataExporter extends DataExporter implements DataExporterInterface {

  /**
   * TaxonomyTermsDataExporter constructor.
   */
  public function __construct() {
    $this->header = array(
      'package' => dt('Package'),
      'machine_name' => dt('Machine name'),
      'label' => dt('Label'),
      'type' => dt('Type'),
      'status' => dt('Status'),
      'core' => dt('Core version'),
      'version' => dt('Version'),
      'description' => dt('Description'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function process() {
    $extensions = drush_get_extensions(FALSE);
    uasort($extensions, '_drush_pm_sort_extensions');

    foreach ($extensions as $extension) {
      $this->addRow(array(
        'package' => $extension->info['package'],
        'machine_name' => $extension->name,
        'label' => $extension->info['name'],
        'type' => ucfirst(drush_extension_get_type($extension)),
        'status' => ucfirst(drush_get_extension_status($extension)),
        'core' => $extension->info['core'],
        'version' => $extension->info['version'],
        'description' => $extension->info['description'],
      ));
    }
  }

}
