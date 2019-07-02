<?php

namespace Drush\dmt_structure_export\TableBuilder;

use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * ModulesTableBuilder class.
 */
class ModulesTableBuilder extends TableBuilder {

  /**
   * Drupal\Core\Extension\ModuleExtensionList definition.
   *
   * @var \Drupal\Core\Extension\ModuleExtensionList
   */
  protected $moduleExtensionList;

  /**
   * The theme handler.
   *
   * @var \Drupal\Core\Extension\ThemeHandler
   */
  protected $themeHandler;

  /**
   * ModulesTableBuilder constructor.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The service container.
   * @param \Drupal\Core\Extension\ModuleExtensionList $module_extension_list
   *   The module extension list.
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   *   The theme handler.
   */
  public function __construct(ContainerInterface $container, ModuleExtensionList $module_extension_list, ThemeHandlerInterface $theme_handler) {
    parent::__construct($container);
    $this->moduleExtensionList = $module_extension_list;
    $this->themeHandler = $theme_handler;

    $this->header = [
      'package' => $this->t('Package'),
      'machine_name' => $this->t('Machine name'),
      'label' => $this->t('Label'),
      'type' => $this->t('Type'),
      'status' => $this->t('Status'),
      'core' => $this->t('Core version'),
      'version' => $this->t('Version'),
      'description' => $this->t('Description'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container,
      $container->get('extension.list.module'),
      $container->get('theme_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildRows() {
    $this->rows = [];

    $modules = $this->moduleExtensionList->reset()->getList();
    $themes = $this->themeHandler->rebuildThemeData();
    $both = array_merge($modules, $themes);

    /** @var \Drupal\Core\Extension\Extension $extension */
    foreach ($both as $key => $extension) {
      $this->rows[$key] = [
        'package' => $extension->info['package'] ?? '',
        'machine_name' => $extension->getName(),
        'label' => $extension->info['name'],
        'type' => $extension->getType(),
        'status' => $extension->status ? 'Enabled' : 'Disabled',
        'core' => $extension->info['core'] ?? '',
        'version' => $extension->info['version'],
        'description' => $extension->info['description'],
      ];
    }

    return $this->rows;
  }

}
