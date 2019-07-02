<?php

namespace Drush\dmt_structure_export\TableBuilder;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drush\dmt_structure_export\Utilities;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * FieldsTableBuilder class.
 */
class FieldsTableBuilder extends TableBuilder {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * FieldsTableBuilder constructor.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The service container.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(ContainerInterface $container, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($container);
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function buildHeader() {
    $this->header = [
      'field_id' => dt('Field ID'),
      'field_name' => dt('Field name'),
      'field_entity_type' => dt('Entity type'),
      'field_type' => dt('Field type'),
      'field_module' => dt('Field module'),
      'field_cardinality' => dt('Field cardinality'),
      'field_translatable' => dt('Translatable'),
      'field_count' => dt('Field count'),
      'field_used_in' => dt('Used in'),
    ];
    return $this->header;
  }

  /**
   * {@inheritdoc}
   */
  protected function buildRows() {
    $this->rows = [];

    $field_storage_configs = $this->entityTypeManager->getStorage('field_storage_config')->loadMultiple();

    /** @var \Drupal\field\FieldStorageConfigInterface $field_storage */
    foreach ($field_storage_configs as $field_storage) {
      $row = [];
      $row['field_id'] = $field_storage->id();
      $field_name = $field_storage->getName();
      $row['field_name'] = $field_name;
      $field_entity_type = $field_storage->getTargetEntityTypeId();
      $row['field_entity_type'] = $field_entity_type;
      $row['field_type'] = $field_storage->getType();
      $row['field_module'] = $field_storage->getTypeProvider();
      $cardinality = $field_storage->getCardinality();
      $row['field_cardinality'] = $cardinality === FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED ? 'UNLIMITED' : $cardinality;
      $row['field_translatable'] = $field_storage->isTranslatable() ? 'TRUE' : 'FALSE';
      $column = current(array_keys($field_storage->getColumns()));
      $field_condition = $field_name;
      $field_condition .= !empty($column) ? ".$column" : '';
      $row['field_count'] = Utilities::getEntityPropertyDataCount($field_entity_type, $field_condition);
      $row['field_used_in'] = implode(', ', $field_storage->getBundles());

      $this->rows[] = $row;
    }

    return $this->rows;
  }

}
