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
    'description' => 'Exports an overview of the site structure.',
    'bootstrap'   => DRUSH_BOOTSTRAP_DRUPAL_FULL,
    'drupal dependencies' => array(),
    'options' => array(
      'destination' => 'Destination path for the export folder.',
    ),
  );

  $items['dmt-export-entities'] = array(
    'description' => 'Exports all entities and fields for this website.',
    'bootstrap'   => DRUSH_BOOTSTRAP_DRUPAL_FULL,
    'drupal dependencies' => array('entity'),
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
  $options['destination'] = _dmt_site_structure_get_destination();
  Controller::exportOverview($options);
  drush_log(dt('Exported the site overview to @path', array('@path' => $options['destination'])), 'success');
}

/**
 * Call-back function for dmt-structure-export-entities command.
 */
function drush_dmt_structure_export_dmt_export_entities() {
  $options = array();
  $options['destination'] = _dmt_site_structure_get_destination();
  Controller::exportEntities($options);
  drush_log(dt('Exported the site entities to @path', array('@path' => $options['destination'])), 'success');
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
    $dest = realpath(drush_get_context('DRUSH_OLDCWD', getcwd())) . '/' . $destination;
  }

  // Create the destination dir if needed.
  if (!is_dir($dest)) {
    drush_op('mkdir', $dest);
    drush_log(dt('Directory @path was created', array('@path' => $dest)), 'info');
  }

  return $dest;
}
