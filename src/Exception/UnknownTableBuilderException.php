<?php

namespace Drush\dmt_structure_export\Exception;

/**
 * Indicates that the requested TableBuilder does not exist.
 */
class UnknownTableBuilderException extends \Exception {

  /**
   * UnknownFormatException constructor.
   *
   * @param string $id
   *   The TableBuilder ID.
   */
  public function __construct($id) {
    $message = dt('The requested TableBuilder "@id" is not available.', ['@id' => $id]);
    parent::__construct($message);
  }

}
