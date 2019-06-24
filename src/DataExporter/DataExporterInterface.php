<?php

namespace Drush\dmt_structure_export\DataExporter;

/**
 * DataExporterInterface definition.
 */
interface DataExporterInterface {

  /**
   * Sets the header.
   *
   * @param array $header
   *   An associative array where keys are used to identify row elements and
   *   values are header labels.
   */
  public function setHeader(array $header);

  /**
   * Returns the header.
   */
  public function getHeader();

  /**
   * Returns the rows.
   */
  public function getRows();

  /**
   * Sets the rows.
   */
  public function setRows($rows);

  /**
   * Adds a row.
   */
  public function addRow(array $row);

  /**
   * Clears the rows.
   */
  public function clearRows();

  /**
   * Process and generate the rows.
   */
  public function process();

}
