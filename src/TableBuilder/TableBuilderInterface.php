<?php

namespace Drush\dmt_structure_export\TableBuilder;

/**
 * TableBuilderInterface definition.
 */
interface TableBuilderInterface {

  /**
   * Builds the header array.
   */
  public function buildHeader();

  /**
   * Builds the rows array.
   */
  public function buildRows();

}
