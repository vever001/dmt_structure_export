<?php

namespace Drush\dmt_structure_export\TableBuilder;

use Drush\dmt_structure_export\Utilities;

/**
 * EntityBundlesTableBuilder class.
 */
class EntityBundlesTableBuilder extends TableBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header = [
      'entity' => dt('Entity type'),
      'entity_count' => dt('Entity count'),
      'bundle' => dt('Bundle'),
      'bundle_count' => dt('Bundle count'),
      'multilingual_enabled' => dt('Multilingual enabled'),
      'multilingual_type' => dt('Multilingual type'),
      'comment_settings' => dt('Comment settings'),
      'revisions_enabled' => dt('Revisions enabled'),
      'moderation_enabled' => dt('Workbench moderation enabled'),
    ];

    $this->setHeader($header);
  }

  /**
   * {@inheritdoc}
   */
  public function buildRows() {
    $rows = [];
    $entity_info = entity_get_info();

    foreach ($entity_info as $entity_type => $et_info) {
      $has_bundles = !empty($et_info['bundles']) && count($et_info['bundles']) > 1;
      $entity_row = [
        'entity' => $et_info['label'] . ' (' . $entity_type . ')',
        'entity_count' => Utilities::getEntityDataCount($entity_type),
      ];

      if (!$has_bundles) {
        // Multilingual settings.
        $multilingual_type = $this->getEntityTranslationType($entity_type);
        $entity_row['multilingual_enabled'] = $multilingual_type ? 'TRUE' : 'FALSE';
        $entity_row['multilingual_type'] = $multilingual_type;
      }

      $rows[] = $entity_row;

      // Process bundles.
      if ($has_bundles) {
        foreach ($et_info['bundles'] as $bundle => $bundle_info) {
          $bundle_row = [
            'bundle' => $bundle_info['label'] . ' (' . $bundle . ')',
            'bundle_count' => Utilities::getEntityDataCount($entity_type, $bundle),
          ];

          // Multilingual settings.
          $multilingual_type = $this->getEntityTranslationType($entity_type, $bundle);
          $bundle_row['multilingual_enabled'] = $multilingual_type ? 'TRUE' : 'FALSE';
          $bundle_row['multilingual_type'] = $multilingual_type;

          // Entity type specific process.
          switch ($entity_type) {
            case 'node':
              $bundle_row += $this->buildNodeBundleRow($bundle);
              break;
          }

          $rows[] = $bundle_row;
        }
      }
    }

    $this->setRows($rows);
  }

  /**
   * Processes a node bundle row.
   *
   * @param $bundle
   *   The node's bundle.
   *
   * @return array
   *   The node bundle row.
   */
  protected function buildNodeBundleRow($bundle) {
    $row = [];

    // Comments.
    if (module_exists('comment')) {
      $comment_enabled = variable_get('comment_' . $bundle, COMMENT_NODE_OPEN);
      $comment_enabled_options = [
        COMMENT_NODE_OPEN => dt('Open'),
        COMMENT_NODE_CLOSED => dt('Closed'),
        COMMENT_NODE_HIDDEN => dt('Hidden'),
      ];
      $row['comment_settings'] = isset($comment_enabled_options[$comment_enabled]) ? $comment_enabled_options[$comment_enabled] : dt('Unknown');
    }

    // Revisions.
    $node_options = variable_get('node_options_' . $bundle, [
      'status',
      'promote',
    ]);
    $row['revisions_enabled'] = in_array('revision', $node_options) ? 'TRUE' : 'FALSE';

    // Moderation.
    if (module_exists('workbench_moderation')) {
      $row['moderation_enabled'] = workbench_moderation_node_type_moderated($bundle) ? 'TRUE' : 'FALSE';
    }

    return $row;
  }

  /**
   * Returns the translation type/handler for the given entity type.
   *
   * @param $entity_type
   *   The entity type.
   *
   * @param null $bundle
   *   The entity's bundle.
   *
   * @return bool|mixed
   *   The entity's translation type.
   */
  protected function getEntityTranslationType($entity_type, $bundle = NULL) {
    switch ($entity_type) {
      case 'node':
        return $this->getEntityTranslationTypeNode($bundle);

      case 'taxonomy_term':
        return $this->getEntityTranslationTypeTaxonomyTerm($bundle);

      default:
        return $this->getEntityTranslationTypeEntityTranslation($entity_type, $bundle);
    }
  }

  /**
   * Returns the translation type/handler for a given node bundle.
   *
   * @param $bundle
   *   The node's bundle.
   *
   * @return mixed
   *   The node's translation type/handler.
   */
  protected function getEntityTranslationTypeNode($bundle) {
    $multilingual_type = variable_get('language_content_type_' . $bundle, 0);
    if ($multilingual_type) {
      $multilingual_type_options = [
        1 => dt('Enabled (no translations)'),
        TRANSLATION_ENABLED => dt('Enabled, with translation (translation module)'),
        ENTITY_TRANSLATION_ENABLED => dt('Enabled, with field translation (entity_translation module)'),
      ];
      return isset($multilingual_type_options[$multilingual_type]) ? $multilingual_type_options[$multilingual_type] : dt('Unknown');
    }
  }

  /**
   * Returns the translation type/handler for a given taxonomy term bundle.
   *
   * @param $bundle
   *   The taxonomy term's bundle.
   *
   * @return bool|mixed
   *   The taxonomy term's translation type/handler.
   */
  protected function getEntityTranslationTypeTaxonomyTerm($bundle) {
    if ($multilingual_type = $this->getEntityTranslationTypeTaxonomyTermI18n($bundle)) {
      return $multilingual_type;
    }
    elseif ($multilingual_type = $this->getEntityTranslationTypeEntityTranslation('taxonomy_term', $bundle)) {
      return $multilingual_type;
    }
  }

  /**
   * Returns the entity translation type/handler for a given entity type.
   *
   * @param $entity_type
   *   The entity type.
   * @param null $bundle
   *   The entity bundle.
   *
   * @return bool|string
   *   The response string or false.
   */
  protected function getEntityTranslationTypeEntityTranslation($entity_type, $bundle = NULL) {
    if (!module_exists('entity_translation') || !entity_translation_enabled($entity_type)) {
      return FALSE;
    }

    if (!empty($bundle) && !entity_translation_enabled_bundle($entity_type, $bundle)) {
      return FALSE;
    }

    return dt('Enabled, with field translation (entity_translation module)');
  }

  /**
   * Returns the i18n translation type for a given vocabulary.
   *
   * @param $vocabulary_name
   *   The vocabulary's name.
   *
   * @return bool|mixed
   *   The i18n translation type of the given vocabulary or false.
   */
  protected function getEntityTranslationTypeTaxonomyTermI18n($vocabulary_name) {
    if (module_exists('i18n_taxonomy')) {
      $vocabulary = taxonomy_vocabulary_machine_name_load($vocabulary_name);
      if ($vocabulary) {
        $multilingual_type = i18n_taxonomy_vocabulary_mode($vocabulary->vid);
        if ($multilingual_type) {
          $multilingual_type_options = [
            I18N_MODE_LOCALIZE => dt('Localize (i18n_taxonomy module)'),
            I18N_MODE_TRANSLATE => dt('Translate (i18n_taxonomy module)'),
            I18N_MODE_MULTIPLE => dt('Translate and Localize (i18n_taxonomy module)'),
            I18N_MODE_LANGUAGE => dt('Fixed Language (i18n_taxonomy module)'),
            I18N_MODE_ENTITY_TRANSLATION => dt('Enabled, with field translation (entity_translation module)'),
          ];
          return isset($multilingual_type_options[$multilingual_type]) ? $multilingual_type_options[$multilingual_type] : dt('Unknown');
        }
      }
    }

    return FALSE;
  }

}
