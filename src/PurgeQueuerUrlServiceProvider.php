<?php

/**
 * @file
 * Contains \Drupal\purge_queuer_url\PurgeQueuerUrlServiceProvider.
 */

namespace Drupal\purge_queuer_url;

use Drupal\Core\DependencyInjection\ServiceModifierInterface;
use Drupal\Core\DependencyInjection\ContainerBuilder;

/**
 *
 */
class PurgeQueuerUrlServiceProvider implements ServiceModifierInterface {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    // $container->removeDefinition('purge_queuer_url.queuer');
  }

}
