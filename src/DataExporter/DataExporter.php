<?php

namespace Drush\dmt_structure_export\DataExporter;

/**
 * DataExporter class.
 */
abstract class DataExporter implements DataExporterInterface, \ArrayAccess {

  /**
   * The header array.
   *
   * @var array
   */
  protected $header = array();

  /**
   * The rows array.
   *
   * @var array|\Traversable
   */
  protected $rows = array();

  /**
   * {@inheritdoc}
   */
  public function setHeader(array $header) {
    $this->header = $header;
  }

  /**
   * {@inheritdoc}
   */
  public function getHeader() {
    return $this->header;
  }

  /**
   * {@inheritdoc}
   */
  public function getRows() {
    return $this->rows;
  }

  /**
   * {@inheritdoc}
   */
  public function setRows($rows) {
    $this->rows = $rows;
  }

  /**
   * {@inheritdoc}
   */
  public function addRow(array $row) {
    $base_row = array_fill_keys(array_keys($this->header), '');
    $this->rows[] = array_merge($base_row, $row);
  }

  /**
   * {@inheritdoc}
   */
  public function clearRows() {
    $this->rows = array();
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
