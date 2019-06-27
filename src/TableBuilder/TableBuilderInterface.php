<?php

namespace Drush\dmt_structure_export\TableBuilder;

/**
 * TableBuilderInterface definition.
 */
interface TableBuilderInterface {

  /**
   * Returns the header.
   */
  public function getHeader();

  /**
   * Sets the header.
   *
   * @param array $header
   *   An associative array where keys are used to identify row elements and
   *   values are header labels.
   */
  public function setHeader(array $header);

  /**
   * Returns the rows.
   */
  public function getRows();

  /**
   * Sets the rows.
   *
   * @param array $rows
   *   An array of rows.
   */
  public function setRows(array $rows);

  /**
   * Builds rows.
   *
   * @return array
   *   The rows as array.
   */
  public function buildRows();

  /**
   * Gets the complete table (header in first line + rows).
   *
   * @return array
   *   The table as array.
   */
  public function getTable();

}
