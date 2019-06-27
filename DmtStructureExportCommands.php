<?php

namespace Drush\Commands\dmt_structure_export;

use Consolidation\OutputFormatters\FormatterManager;
use Consolidation\OutputFormatters\Options\FormatterOptions;
use Consolidation\OutputFormatters\StructuredData\RowsOfFields;
use Drush\Commands\DrushCommands;
use Drush\dmt_structure_export\TableBuilder\TableBuilderManager;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;

/**
 * Drush commands for DMT Structure Export.
 */
class DmtStructureExportCommands extends DrushCommands {

  /**
   * Default directory for the exported files.
   */
  const DMT_STRUCTURE_EXPORT_DEFAULT_DIR = 'dmt_structure_export';

  /**
   * Exports website structure/data information to CSV or table of fields.
   *
   * The default formatter is CSV.
   *
   * @param string $export_type
   *   An export to generate. See \Drush\dmt_structure_export\TableBuilder\
   *   TableBuilderManager::getTableBuilderTypes().
   * @param array $options
   *   An array of options.
   *
   * @option format Use the specified output format.
   *
   * @command dmt-se:export
   *
   * @bootstrap DRUSH_BOOTSTRAP_DRUPAL_FULL
   *
   * @usage dmt-se:export
   * @usage dmt-se:export entity_bundles --format=table
   *
   * @return \Consolidation\OutputFormatters\StructuredData\RowsOfFields
   *   The data as RowsOfFields.
   */
  public function export($export_type, array $options = ['format' => 'csv']) {
    try {
      drush_autoload(__FILE__);
      $table_builder = TableBuilderManager::createInstance($export_type);
      $table_builder->buildRows();
      $table = $table_builder->getTable();
      return new RowsOfFields($table);
    }
    catch (\Exception $e) {
      $this->logger()->error($e->getMessage());
    }
  }

  /**
   * Exports all.
   *
   * @param array $options
   *   An array of options.
   *
   * @option destination Relative or absolute path to the folder where CSVs will be generated.
   *
   * @command dmt-se:export-all
   *
   * @bootstrap DRUSH_BOOTSTRAP_DRUPAL_FULL
   */
  public function exportAll(array $options = ['destination' => '', 'format' => 'csv']) {
    try {
      var_dump($options['destination']);
      drush_autoload(__FILE__);
      $dst_directory = $this->getDestinationDirectory();

      $table_types = TableBuilderManager::getTableBuilderTypes();
      foreach ($table_types as $key => $table_type) {
        // Call the export command.
        /** @var \Consolidation\OutputFormatters\StructuredData\RowsOfFields $data */
        $data = drush_op([$this, 'export'], $key, $options);

        // Write to CSV.
        $file_path = $dst_directory . '/' . $key . '.csv';
        $output = new StreamOutput(fopen($file_path, 'w'));
        $this->formatRowsOfFields($output, 'csv', $data, new FormatterOptions());
      }
    }
    catch (\Exception $e) {
      $this->logger()->error($e->getMessage());
    }
  }

  /**
   * Formats a given RowsOfFields object.
   *
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   *   Output stream to write to.
   * @param string $format
   *   Data format to output in.
   * @param \Consolidation\OutputFormatters\StructuredData\RowsOfFields $data
   *   The RowsOfFields object to format.
   * @param \Consolidation\OutputFormatters\Options\FormatterOptions $options
   *   Formatter options.
   *
   * @throws \Consolidation\OutputFormatters\Exception\InvalidFormatException
   */
  protected function formatRowsOfFields(OutputInterface $output, $format, RowsOfFields $data, FormatterOptions $options) {
    /** @var \Consolidation\OutputFormatters\StructuredData\TableDataInterface $table_transformer */
    $table_transformer = $data->restructure($options);
    $table_data = $table_transformer->getTableData();
    $formatterManager = new FormatterManager();
    $formatterManager->write($output, $format, $table_data, $options);
  }

  /**
   * Returns the destination folder.
   */
  protected function getDestinationDirectory() {
    $destination = drush_get_option('destination', self::DMT_STRUCTURE_EXPORT_DEFAULT_DIR);

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
      $this->logger()->info(dt('Directory @path was created', ['@path' => $dest]));
    }

    return $dest;
  }

}
