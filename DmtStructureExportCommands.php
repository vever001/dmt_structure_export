<?php

namespace Drush\Commands\dmt_structure_export;

use Consolidation\OutputFormatters\FormatterManager;
use Consolidation\OutputFormatters\Options\FormatterOptions;
use Consolidation\OutputFormatters\StructuredData\RowsOfFields;
use Drush\Commands\DrushCommands;
use Drush\dmt_structure_export\TableBuilder\TableBuilderManager;
use Drush\Log\LogLevel;
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
      drush_log($e->getMessage(), LogLevel::ERROR);
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
      drush_autoload(__FILE__);
      $dst_dir = $this->getDestinationDirectory($options['destination']);

      $table_types = TableBuilderManager::getTableBuilderTypes();
      foreach ($table_types as $key => $table_type) {
        // Call the export command.
        /** @var \Consolidation\OutputFormatters\StructuredData\RowsOfFields $data */
        $data = drush_op([$this, 'export'], $key, $options);

        // Write to CSV.
        $file_path = $dst_dir . '/' . $key . '.csv';
        $output = new StreamOutput(fopen($file_path, 'w'));
        $this->formatRowsOfFields($output, 'csv', $data, new FormatterOptions());
        drush_log(dt('Exported CSV file to @path', array('@path' => $file_path)), LogLevel::SUCCESS);
      }
    }
    catch (\Exception $e) {
      drush_log($e->getMessage(), LogLevel::ERROR);
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
  protected function getDestinationDirectory($destination) {
    $dst_dir = !empty($destination) ? $destination : self::DMT_STRUCTURE_EXPORT_DEFAULT_DIR;

    // Handle relative or absolute paths.
    if (strpos($dst_dir, '/') !== 0) {
      $dst_dir = drush_cwd() . '/' . $dst_dir;
    }

    // Create the destination dir if needed.
    if (!is_dir($dst_dir)) {
      drush_mkdir($dst_dir);
      drush_log(dt('Directory @path was created', ['@path' => $dst_dir]), LogLevel::INFO);
    }

    return $dst_dir;
  }

}
