<?php

namespace Drush\dmt_structure_export\TableBuilder;

use Drupal\Core\Config\Entity\ConfigEntityTypeInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\field\FieldStorageConfigInterface;
use Drush\dmt_structure_export\Utilities;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * EntityPropertiesTableBuilder class.
 */
class EntityPropertiesTableBuilder extends TableBuilder {

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity bundle info.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $entityTypeBundleInfo;

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * EntityPropertiesTableBuilder constructor.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The service container.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle info service.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager service.
   */
  public function __construct(ContainerInterface $container, EntityTypeManagerInterface $entityTypeManager, EntityTypeBundleInfoInterface $entity_type_bundle_info, EntityFieldManagerInterface $entity_field_manager) {
    parent::__construct($container);
    $this->entityTypeManager = $entityTypeManager;
    $this->entityTypeBundleInfo = $entity_type_bundle_info;
    $this->entityFieldManager = $entity_field_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container,
      $container->get('entity_type.manager'),
      $container->get('entity_type.bundle.info'),
      $container->get('entity_field.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function buildHeader() {
    $this->header = [
      // Entity data.
      'entity' => dt('Entity type'),
      'entity_count' => dt('Entity count'),
      'bundle' => dt('Bundle'),
      'bundle_count' => dt('Bundle count'),
      // Property data.
      'property_id' => dt('Property ID'),
      'property_label' => dt('Property Label'),
      'property_type' => dt('Property type'),
      'property_translatable' => dt('Property translatable'),
      'property_required' => dt('Property required'),
      'property_count' => dt('Property count'),
      // Field data.
      'property_field' => dt('Is field?'),
      'property_field_type' => dt('Field Type'),
      'property_field_module' => dt('Field module'),
      'property_field_cardinality' => dt('Field cardinality'),
    ];
    return $this->header;
  }

  /**
   * {@inheritdoc}
   */
  protected function buildRows() {
    $this->rows = [];
    $rows = $this->buildEntityRows();
    $this->rows = $this->flattenRows($rows);
    return $this->rows;
  }

  /**
   * Builds all entity rows.
   */
  protected function buildEntityRows() {
    $row = [];
    $entity_definitions = $this->entityTypeManager->getDefinitions();
    foreach ($entity_definitions as $entity_type => $entity_definition) {
      $row[$entity_type] = $this->buildEntityRow($entity_type);
    }
    return $row;
  }

  /**
   * Builds an entity row.
   */
  protected function buildEntityRow($entity_type) {
    $row = [];
    $row['entity'] = $entity_type;
    $row['entity_count'] = Utilities::getEntityDataCount($entity_type);
    $row['bundles'] = $this->buildEntityBundleRows($entity_type);
    return $row;
  }

  /**
   * Builds all entity bundle rows.
   */
  protected function buildEntityBundleRows($entity_type) {
    $row = [];
    $bundles = $this->entityTypeBundleInfo->getBundleInfo($entity_type);
    foreach ($bundles as $bundle_id => $bundle_label) {
      $row[$bundle_id] = $this->buildEntityBundleRow($entity_type, $bundle_id);
    }
    return $row;
  }

  /**
   * Builds an entity bundle row.
   */
  protected function buildEntityBundleRow($entity_type, $bundle_id) {
    $row = [];
    $row['bundle'] = $bundle_id;
    $row['bundle_count'] = Utilities::getEntityDataCount($entity_type, $bundle_id);
    $row['bundle_properties'] = $this->buildEntityBundlePropertyRows($entity_type, $bundle_id);
    return $row;
  }

  /**
   * Builds all entity bundle properties rows.
   */
  protected function buildEntityBundlePropertyRows($entity_type, $bundle_id) {
    $row = [];
    $entity_definitions = $this->entityTypeManager->getDefinitions();
    $entity_definition = $entity_definitions[$entity_type];

    if ($entity_definition->entityClassImplements(FieldableEntityInterface::class)) {
      $fields = $this->entityFieldManager->getFieldDefinitions($entity_type, $bundle_id);
      foreach ($fields as $field) {
        // Skip computed fields.
        if ($field->isComputed()) {
          continue;
        }

        $field_storage = $field->getFieldStorageDefinition();
        $field_name = $field->getName();
        $field_entity_type = $field->getTargetEntityTypeId();

        $property_row = [];
        $property_row['property_id'] = $field_name;
        $property_row['property_label'] = $field->getLabel();
        $property_row['property_type'] = $field->getType();
        $property_row['property_translatable'] = $field->isTranslatable() ? 'TRUE' : 'FALSE';
        $property_row['property_required'] = $field->isRequired() ? 'TRUE' : 'FALSE';
        $is_field = $field_storage instanceof FieldStorageConfigInterface;
        $property_row['property_field'] = $is_field ? 'TRUE' : 'FALSE';
        $property_row['property_field_type'] = $field->getType();

        if ($is_field) {
          $property_row['property_field_module'] = $field_storage->getTypeProvider();
        }

        $cardinality = $field_storage->getCardinality();
        $property_row['property_field_cardinality'] = $cardinality === FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED ? 'UNLIMITED' : $cardinality;

        $field_columns = $field_storage->getColumns();
        foreach ($field_columns as $field_column => $field_column_info) {
          $property_column_row = $property_row;
          $property_column_row['property_id'] = $field_name . '/' . $field_column;
          $property_column_row['property_label'] = $field->getLabel() . ' / ' . $field_column;
          $property_column_row['property_type'] = $field_column_info['type'];

          if ($field_storage->hasCustomStorage()) {
            $field_condition = $field_name . '.' . $field_column;
            $property_column_row['property_count'] = Utilities::getEntityPropertyDataCount($field_entity_type, $field_condition, $bundle_id);
          }

          $row[$field_name . '.' . $field_column] = $property_column_row;
        }
      }
    }
    elseif ($entity_definition instanceof ConfigEntityTypeInterface) {
      $properties = $entity_definition->getPropertiesToExport();
      if ($properties) {
        foreach ($properties as $property) {
          if (!in_array($property, [
            '_core',
            'third_party_settings',
            'dependencies',
            'status',
          ])) {
            $property_row = [];
            $property_row['property_id'] = $property;
            $property_row['property_label'] = $property;
            $row[$property] = $property_row;
          }
        }
      }
    }

    return $row;
  }

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
