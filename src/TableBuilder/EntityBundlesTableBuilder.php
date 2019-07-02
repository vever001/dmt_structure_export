<?php

namespace Drush\dmt_structure_export\TableBuilder;

use Drupal\comment\Plugin\Field\FieldType\CommentItemInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drush\dmt_structure_export\Utilities;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * EntityBundlesTableBuilder class.
 */
class EntityBundlesTableBuilder extends TableBuilder {

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

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
   * EntityBundlesTableBuilder constructor.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The service container.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $entity_type_bundle_info
   *   The entity type bundle info service.
   */
  public function __construct(ContainerInterface $container, ModuleHandlerInterface $moduleHandler, EntityTypeManagerInterface $entityTypeManager, EntityTypeBundleInfoInterface $entity_type_bundle_info) {
    parent::__construct($container);
    $this->moduleHandler = $moduleHandler;
    $this->entityTypeManager = $entityTypeManager;
    $this->entityTypeBundleInfo = $entity_type_bundle_info;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container,
      $container->get('module_handler'),
      $container->get('entity_type.manager'),
      $container->get('entity_type.bundle.info')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function buildHeader() {
    $this->header = [
      'entity' => dt('Entity type'),
      'entity_count' => dt('Entity count'),
      'bundle' => dt('Bundle'),
      'bundle_count' => dt('Bundle count'),
      'multilingual_enabled' => dt('Multilingual enabled'),
      'multilingual_type' => dt('Multilingual type'),
      'comment_settings' => dt('Comment settings'),
      'revisions_enabled' => dt('Revisions enabled'),
      'moderation_enabled' => dt('Content moderation enabled'),
    ];
    return $this->header;
  }

  /**
   * {@inheritdoc}
   */
  protected function buildRows() {
    $this->rows = [];

    $entity_definitions = $this->entityTypeManager->getDefinitions();
    foreach ($entity_definitions as $entity_type => $entity_definition) {
      $bundles = $this->entityTypeBundleInfo->getBundleInfo($entity_type);
      $bundle_key = $entity_definition->getKey('bundle');
      $has_bundles = !empty($bundles) && count($bundles) > 1 && !empty($bundle_key);

      $entity_row = [
        'entity' => $entity_definition->getLabel() . ' (' . $entity_type . ')',
        'entity_count' => Utilities::getEntityDataCount($entity_type),
      ];

      if (!$has_bundles) {
        // Multilingual settings.
        $translation_enabled = $this->isContentTranslationEnabled($entity_type);
        $entity_row['multilingual_enabled'] = $translation_enabled ? 'TRUE' : 'FALSE';
        if ($translation_enabled) {
          $entity_row['multilingual_type'] = $this->t('Enabled, with field translation (content_translation)');
        }

        // Content moderation settings.
        $moderation_enabled = !empty(array_column($bundles, 'workflow'));
        $entity_row['moderation_enabled'] = $moderation_enabled ? 'TRUE' : 'FALSE';

        // Revision settings.
        $entity_row['revisions_enabled'] = $moderation_enabled || $entity_definition->hasKey('revision') ? 'TRUE' : 'FALSE';

        // Comment settings.
        $entity_row['comment_settings'] = $this->getCommentSettings($entity_type);
      }

      $this->rows[] = $entity_row;

      // Process bundles.
      if ($has_bundles) {
        foreach ($bundles as $bundle_id => $bundle_info) {
          $bundle_row = [
            'bundle' => $bundle_info['label'] . ' (' . $bundle_id . ')',
            'bundle_count' => Utilities::getEntityDataCount($entity_type, $bundle_id),
          ];

          // Multilingual settings.
          $translation_enabled = $this->isContentTranslationEnabled($entity_type, $bundle_id);
          $bundle_row['multilingual_enabled'] = $translation_enabled ? 'TRUE' : 'FALSE';
          if ($translation_enabled) {
            $bundle_row['multilingual_type'] = $this->t('Enabled, with field translation (content_translation)');
          }

          // Content moderation settings.
          $moderation_enabled = !empty($bundle_info['workflow']);
          $bundle_row['moderation_enabled'] = $moderation_enabled ? 'TRUE' : 'FALSE';

          // Revision settings.
          $bundle_row['revisions_enabled'] = $moderation_enabled || $entity_definition->hasKey('revision') ? 'TRUE' : 'FALSE';

          // Comment settings.
          $bundle_row['comment_settings'] = $this->getCommentSettings($entity_type, $bundle_id);

          $this->rows[] = $bundle_row;
        }
      }
    }

    return $this->rows;
  }

  /**
   * Returns whether content_translation is enabled for the given entity type.
   */
  protected function isContentTranslationEnabled($entity_type, $bundle = NULL) {
    if (!$this->moduleHandler->moduleExists('content_translation')) {
      return FALSE;
    }

    /** @var \Drupal\content_translation\ContentTranslationManagerInterface $content_translation_manager */
    $content_translation_manager = $this->container->get('content_translation.manager');
    return $content_translation_manager->isEnabled($entity_type, $bundle);
  }

  /**
   * Returns the comment settings for the given entity type and bundle.
   */
  protected function getCommentSettings($entity_type, $bundle = NULL) {
    if (!$this->moduleHandler->moduleExists('comment')) {
      return;
    }

    $entity_type_definition = $this->entityTypeManager->getDefinition($entity_type);
    if (!$entity_type_definition->entityClassImplements(FieldableEntityInterface::class)) {
      return;
    }

    /** @var \Drupal\Core\Entity\EntityFieldManager $entity_field_manager */
    $entity_field_manager = $this->container->get('entity_field.manager');
    $entity_fields = $entity_field_manager->getFieldDefinitions($entity_type, $bundle);
    $comment_options = [
      CommentItemInterface::HIDDEN => $this->t('Hidden'),
      CommentItemInterface::CLOSED => $this->t('Closed'),
      CommentItemInterface::OPEN => $this->t('Open'),
    ];

    $comment_manager = $this->container->get('comment.manager');
    $comment_fields = $comment_manager->getFields($entity_type);
    $comment_settings = [];
    foreach ($comment_fields as $comment_field => $comment_field_settings) {
      if (isset($entity_fields[$comment_field])) {
        /** @var \Drupal\field\Entity\FieldConfig $entity_field */
        $entity_field = $entity_fields[$comment_field];
        $comment_status = $entity_field->getDefaultValueLiteral()[0]['status'];
        $comment_status = $comment_options[$comment_status] ?? $this->t('Unknown');
        $comment_settings[] = sprintf('%s (%s)', $comment_field, $comment_status);
      }
    }

    return implode(', ', $comment_settings);
  }

}
