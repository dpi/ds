<?php

/**
 * @file
 * Contains \Drupal\ds\Plugin\DsPluginManager.
 */

namespace Drupal\ds\Plugin;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Plugin type manager for all ds plugins.
 */
class DsPluginManager extends DefaultPluginManager {

  /**
   * Constructs a new \Drupal\block\Plugin\Type\DsPluginManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Language\LanguageManager $language_manager
   *   The language manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, LanguageManager $language_manager, ModuleHandlerInterface $module_handler) {
    $annotation_namespaces = array(
      'Drupal\ds\Annotation' => DRUPAL_ROOT . '/modules/ds/lib',
    );

    parent::__construct('PLugin\DsField', $namespaces, $annotation_namespaces, 'Drupal\ds\Annotation\DsField');

    $this->alterInfo($module_handler, 'ds_fields_info');
    $this->setCacheBackend($cache_backend, $language_manager, 'ds_fields_info');
  }

  /**
   * {@inheritdoc}
   */
  protected function findDefinitions() {
    $definitions = parent::findDefinitions();
    $module_handler = \Drupal::moduleHandler();
    foreach ($definitions as $plugin_id => $definition) {
      if (!$module_handler->moduleExists($definition['provider'])) {
        unset($definitions[$plugin_id]);
      }
    }
    return $definitions;
  }

}
