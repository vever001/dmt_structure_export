<?php

namespace Drush\dmt_structure_export;

use Drush\dmt_structure_export\DataExporter\EntitiesDataExporter;
use Drush\dmt_structure_export\DataExporter\FieldsDataExporter;
use Drush\dmt_structure_export\DataExporter\OverviewDataExporter;
use League\Csv\Writer;

/**
 * DMT Structure Export controller.
 */
class Controller {

  /**
   * Exports an overview of the site structure.
   */
  public static function exportOverview(array $options) {
    $path = $options['destination'] . '/overview.csv';
    $writer = Writer::createFromPath($path, 'w+');

    // Generate CSV rows.
    $export = new OverviewDataExporter();
    $export->process();
    $writer->insertOne($export->getHeader());
    $writer->insertAll($export->getRows());
  }

  /**
   * Exports all entities and fields for this website.
   */
  public static function exportEntities(array $options) {
    $path = $options['destination'] . '/entities.csv';
    $writer = Writer::createFromPath($path, 'w+');

    // Generate CSV rows.
    $export = new EntitiesDataExporter();
    $export->process();
    $writer->insertOne($export->getHeader());
    $writer->insertAll($export->getRows());
  }

  /**
   * Exports all entities and fields for this website.
   */
  public static function exportFields(array $options) {
    $path = $options['destination'] . '/fields.csv';
    $writer = Writer::createFromPath($path, 'w+');

    // Generate CSV rows.
    $export = new FieldsDataExporter();
    $export->process();
    $writer->insertOne($export->getHeader());
    $writer->insertAll($export->getRows());
  }

}
