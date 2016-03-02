<?php

/**
 * @file
 * Contains \Drupal\purge_queuer_url\PurgeQueuerUrlServiceProvider.
 */

namespace Drupal\purge_queuer_url;

use Drupal\Core\DependencyInjection\ServiceModifierInterface;
use Drupal\Core\DependencyInjection\ContainerBuilder;

/**
 * Wraps "http_middleware.page_cache" in our URL gathering substitute.
 */
class PurgeQueuerUrlServiceProvider implements ServiceModifierInterface {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    // $container->removeDefinition('purge_queuer_url.queuer');

      // http_middleware.purge_queuer_url_page_cache_wrapper:
      //   class: Drupal\purge_queuer_url\StackMiddleware\PageCacheWrapper
      //   arguments: ['@cache.render', '@page_cache_request_policy', '@page_cache_response_policy']
      //   tags:
      //     - { name: http_middleware, priority: 200, responder: true }

  }

}
