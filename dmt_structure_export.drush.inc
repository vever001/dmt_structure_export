<?php

/**
 * @file
 * Drush 8 commandfile for DMT Structure Export.
 */

use Drush\dmt_structure_export\Controller;

/**
 * Default directory for the exported files.
 */
define('DMT_STRUCTURE_EXPORT_DEFAULT_DIR', 'dmt_structure_export');

/**
 * Implements hook_drush_command().
 */
function dmt_structure_export_drush_command() {
  $items = array();

  $items['dmt-export-overview'] = array(
    'description' => 'Exports an overview of the site structure..',
    'bootstrap'   => DRUSH_BOOTSTRAP_DRUPAL_FULL,
    'drupal dependencies' => array(),
    'options' => array(
      'destination' => 'Destination path for the export folder.',
    ),
  );

  return $items;
}

/**
 * Implements hook_drush_init().
 */
function dmt_structure_export_drush_init() {
  drush_autoload(__FILE__);
}

/**
 * Call-back function for dmt-structure-export-overview command.
 */
function drush_dmt_structure_export_dmt_export_overview() {
  $options = array();
  $destination = drush_get_option('destination', DMT_STRUCTURE_EXPORT_DEFAULT_DIR);

  // Let's see if the given destination is a absolute path.
  if (strpos($destination, '/') === 0) {
    $options['destination'] = $destination;
  }
  else {
    $options['destination'] = realpath(drush_get_context('DRUSH_OLDCWD', getcwd())) . '/' . $destination;
  }

  // Create the destination dir if needed.
  if (!is_dir($options['destination'])) {
    drush_op('mkdir', $options['destination']);
    drush_log(dt('Directory @path was created', array('@path' => $options['destination'])), 'info');
  }

  // Export.
  Controller::exportOverview($options);
  drush_log(dt('TEST OUTPUT'), 'success');
}
