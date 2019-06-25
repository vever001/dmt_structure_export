<?php

/**
 * @file
 * Drush 8 commandfile for DMT Structure Export.
 */

use Drush\dmt_structure_export\DataExporterFactory;
use League\Csv\Writer;

/**
 * Default directory for the exported files.
 */
define('DMT_STRUCTURE_EXPORT_DEFAULT_DIR', 'dmt_structure_export');

/**
 * Implements hook_drush_init().
 */
function dmt_structure_export_drush_init() {
  drush_autoload(__FILE__);
}

/**
 * Implements hook_drush_command().
 */
function dmt_structure_export_drush_command() {
  $items = array();

  $items['dmt-structure-export'] = array(
    'description' => 'Exports the site structure to CSV files.',
    'bootstrap'   => DRUSH_BOOTSTRAP_DRUPAL_FULL,
    'drupal dependencies' => array('entity'),
    'arguments' => array(
      'export_types' => 'A space delimited list of exports to generate. Omit this argument to generate them all.',
    ),
    'options' => array(
      'destination' => 'Destination path for the export folder.',
    ),
  );

  return $items;
}

/**
 * Call-back function for dmt-structure-export command.
 */
function drush_dmt_structure_export() {
  $destination = _dmt_site_structure_get_destination();
  $export_types = func_get_args();
  if (empty($export_types)) {
    $export_types = array_keys(DataExporterFactory::getExportTypes());
  }

  foreach ($export_types as $export_type) {
    $path = $destination . '/' . $export_type . '.csv';

    try {
      $writer = Writer::createFromPath($path, 'w+');
      $exporter = DataExporterFactory::createInstance($export_type);
      $exporter->process();
      $writer->insertOne($exporter->getHeader());
      $writer->insertAll($exporter->getRows());
      drush_log(dt('Exported CSV file to @path', array('@path' => $path)), 'success');
    }
    catch (\Exception $e) {
      drush_log($e->getMessage(), 'error');
    }
  }
}

/**
 * Returns the destination folder.
 */
function _dmt_site_structure_get_destination() {
  $destination = drush_get_option('destination', DMT_STRUCTURE_EXPORT_DEFAULT_DIR);

  // Let's see if the given destination is a absolute path.
  if (strpos($destination, '/') === 0) {
    $dest = $destination;
  }
  else {
    $dest = drush_cwd() . '/' . $destination;
  }

  // Create the destination dir if needed.
  if (!is_dir($dest)) {
    drush_mkdir($dest);
    drush_log(dt('Directory @path was created', array('@path' => $dest)), 'info');
  }

  return $dest;
}
