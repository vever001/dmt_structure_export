<?php

namespace Drush\dmt_structure_export\TableBuilder;

/**
 * Class TableBuilder.
 */
abstract class TableBuilder implements TableBuilderInterface {

  /**
   * The header of the table.
   *
   * @var array
   */
  private $header = [];

  /**
   * The rows of the table.
   *
   * @var array
   */
  private $rows = [];

  /**
   * The whole table (header in first line followed by the rows).
   * @var
   */
  private $table = [];

  /**
   * Returns the header of the table.
   *
   * @return array
   *   The header of the table.
   */
  public function getHeader() {
    return $this->header;
  }

  /**
   * Sets the header of the table.
   *
   * @param array $header
   *   An associative array where keys are used to identify row elements and
   *   values are header labels.
   */
  public function setHeader(array $header) {
    $this->header = $header;
  }

  /**
   * Returns the rows of the table.
   *
   * @return array
   *   The rows of the table.
   */
  public function getRows() {
    return $this->rows;
  }

  /**
   * Sets the rows of the table.
   *
   * @param array $rows
   *   A list of rows.
   */
  public function setRows(array $rows) {
    $this->rows = $rows;
  }

  /**
   * Returns the table to export.
   *
   * @return array
   *   The table to export.
  */
  public function getTable() {
    return $this->table;
  }

  /**
   * Sets the table to export.
   *
   * @param array $table
   *   A list of rows preceded or not by a header.
   */
  public function setTable(array $table) {
    $this->table = $table;
  }

  /**
   * Builds the table to export.
   */
  public function buildTable() {
    $table = [];
    if (!empty($this->getHeader())) {
      $table[] = $this->getHeader();
    }
    if (!empty($this->getRows())) {
      $table = array_merge($table, $this->getRows());
    }
    $this->setTable($table);
  }

  /**
   * Builds the header, the rows and the table.
   */
  public function build() {
    $this->buildHeader();
    $this->buildRows();
    $this->buildTable();
  }

  /**
   * Flattens a list of rows.
   *
   * @param array $rows
   *   The list of rows to be flattened.
   *
   * @return array
   *   The list of flattened rows.
   */
  protected function flattenRows(array $rows) {
    $result = [];
      foreach ($rows as $row) {
        $this->flattenRow($row, $result);
      }

    return $result;
  }

  /**
   * Flattens a single row.
   *
   * Rows may contain nested arrays (unlimited depth), which will be appended
   * and flattened to the $result array.
   *
   * @param array $row
   *   The row to be flattened.
   * @param $result
   *   The list of flattened rows.
   */
  protected function flattenRow(array $row, &$result) {
    $new_row = [];
    $nested_array_keys = [];
    foreach ($row as $key => $value) {
      if (!is_array($value)) {
        $new_row[$key] = $value;
      }
      else {
        $nested_array_keys[] = $key;
      }
    }
    if (!empty($new_row)) {
      $result[] = $new_row;
    }
    if (!empty($nested_array_keys)) {
      foreach ($nested_array_keys as $key) {
        $this->flattenRow($row[$key], $result);
      }
    }
  }

}
