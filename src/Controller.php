<?php

namespace Drush\dmt_structure_export;

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
    $export = new OverviewDataExport();
    $export->process();
    $writer->insertOne($export->getHeader());
    $writer->insertAll($export->getRows());

    drush_log(dt('Exported the site overview to @path', array('@path' => $path)), 'success');
  }

}
