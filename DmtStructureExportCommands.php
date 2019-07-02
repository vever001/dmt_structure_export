<?php

namespace Drush\Commands\dmt_structure_export;

use Composer\Autoload\ClassLoader;
use Consolidation\OutputFormatters\FormatterManager;
use Consolidation\OutputFormatters\Options\FormatterOptions;
use Consolidation\OutputFormatters\StructuredData\RowsOfFields;
use Drush\Commands\DrushCommands;
use Drush\dmt_structure_export\TableBuilderManager;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Drush commands for DMT Structure Export.
 */
class DmtStructureExportCommands extends DrushCommands {

  /**
   * Default directory for the exported files.
   */
  const DMT_STRUCTURE_EXPORT_DEFAULT_DIR = 'dmt_structure_export';

  /**
   * Autoload our files if they are not already loaded.
   *
   * @hook init
   */
  public function init() {
    if (!class_exists(TableBuilderManager::class)) {
      $loader = new ClassLoader();
      $loader->addPsr4('Drush\\dmt_structure_export\\', __DIR__ . '/src');
      $loader->register();
    }
  }

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
      $manager = new TableBuilderManager();
      $table_builder = $manager->getTableBuilder($export_type);
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
    $dst_dir = $this->getDestinationDirectory($options['destination']);
    $manager = new TableBuilderManager();
    $exports = $manager->getTableBuilders();

    foreach ($exports as $export_type => $table_builder) {
      try {
        // Build table.
        $table_builder->buildRows();
        $data = new RowsOfFields($table_builder->getTable());

        // Write to CSV.
        $file_path = $dst_dir . '/' . $export_type . '.csv';
        $output = new StreamOutput(fopen($file_path, 'w'));
        $this->formatData($output, 'csv', $data, new FormatterOptions());
        $this->logger()->success(dt('Exported CSV file to @path', ['@path' => $file_path]));
      }
      catch (\Exception $e) {
        $this->logger()->error($e->getMessage());
      }
    }
  }

  /**
   * Formats and writes RowsOfFields using Consolidation\OutputFormatters.
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
  protected function formatData(OutputInterface $output, $format, RowsOfFields $data, FormatterOptions $options) {
    $formatterManager = new FormatterManager();
    $formatterManager->write($output, $format, $data, $options);
  }

  /**
   * Returns the destination folder.
   */
  protected function getDestinationDirectory($destination) {
    $dst_dir = !empty($destination) ? $destination : self::DMT_STRUCTURE_EXPORT_DEFAULT_DIR;

    // Handle relative or absolute paths.
    if (strpos($dst_dir, '/') !== 0) {
      $dst_dir = $this->getConfig()->cwd() . '/' . $dst_dir;
    }

    // Create the destination dir if needed.
    $fs = new Filesystem();
    if (!$fs->exists($dst_dir)) {
      $fs->mkdir($dst_dir);
      $this->logger()->info(dt('Directory @path was created', ['@path' => $dst_dir]));
    }

    return $dst_dir;
  }

}
