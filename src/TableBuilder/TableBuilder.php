<?php

namespace Drush\dmt_structure_export\TableBuilder;

/**
 * Class TableBuilder.
 */
abstract class TableBuilder implements TableBuilderInterface {

  /**
   * {@inheritdoc}
   */
  protected $header = [];

  /**
   * {@inheritdoc}
   */
  protected $rows = [];

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
  public function setRows(array $rows) {
    $this->rows = $rows;
  }

  /**
   * {@inheritdoc}
   */
  public function getTable() {
    $table = [];

    if (!empty($this->header)) {
      $table[] = $this->header;
    }

    if (!empty($this->rows)) {
      $table = array_merge($table, $this->rows);
    }

    return $table;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $this->buildHeader();
    $this->buildRows();
  }

  /**
   * Builds the header array.
   */
  abstract protected function buildHeader();

  /**
   * Builds the rows array.
   */
  abstract protected function buildRows();

  /**
   * Flattens an array of rows.
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

    return $result;
  }

}
