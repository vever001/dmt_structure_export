<?php

namespace Drush\dmt_structure_export\TableBuilder;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class TableBuilder.
 */
abstract class TableBuilder implements TableBuilderInterface, ContainerInjectionInterface {

  use ContainerAwareTrait;
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  protected $header = [];

  /**
   * {@inheritdoc}
   */
  protected $rows = [];

  /**
   * TableBuilder constructor.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The service container.
   */
  public function __construct(ContainerInterface $container) {
    $this->container = $container;
  }

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

}
