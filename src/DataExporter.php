<?php

namespace Drush\dmt_structure_export;

/**
 * DataExporter class.
 */
abstract class DataExporter implements \ArrayAccess {

  /**
   * The header array.
   *
   * @var array
   */
  protected $header = [];

  /**
   * The rows array.
   *
   * @var array
   */
  protected $rows = [];

  /**
   * Sets the header.
   *
   * @param array $header
   *   An associative array where keys are used to identify row elements and
   *   values are header labels.
   */
  public function setHeader(array $header) {
    $this->header = $header;
  }

  /**
   * Returns the header.
   */
  public function getHeader() {
    return $this->header;
  }

  /**
   * Returns the rows.
   */
  public function getRows() {
    return $this->rows;
  }

  /**
   * Sets the rows.
   */
  public function setRows(array $rows) {
    $this->rows = $rows;
  }

  /**
   * Adds a row.
   */
  public function addRow(array $row) {
    $base_row = array_fill_keys(array_keys($this->header), '');
    $this->rows[] = array_merge($base_row, $row);
  }

  /**
   * Clears the rows.
   */
  public function clearRows() {
    $this->rows = [];
  }

  /**
   * Implements ArrayAccess::offsetExists().
   */
  public function offsetExists($offset) {
    return isset($this->rows[$offset]);
  }

  /**
   * Implements ArrayAccess::offsetGet().
   */
  public function offsetGet($offset) {
    return isset($this->rows[$offset]) ? $this->rows[$offset] : NULL;
  }

  /**
   * Implements ArrayAccess::offsetSet().
   */
  public function offsetSet($offset, $value) {
    $base_row = array_fill_keys(array_keys($this->header), NULL);
    $row = array_merge($base_row, $value);

    if (NULL === $offset) {
      $this->rows[] = $row;
    }
    else {
      $this->rows[$offset] = $row;
    }
  }

  /**
   * Implements ArrayAccess::offsetUnset().
   */
  public function offsetUnset($offset) {
    unset($this->rows[$offset]);
  }

}
