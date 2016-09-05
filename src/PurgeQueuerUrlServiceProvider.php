<?php

namespace Drupal\purge_queuer_url;

use Symfony\Component\DependencyInjection\Reference;
use Drupal\Core\DependencyInjection\ServiceModifierInterface;
use Drupal\Core\DependencyInjection\ContainerBuilder;

/**
 * Replaces "http_middleware.page_cache" with PageCacheUrlRegistrationWrapper.
 */
class PurgeQueuerUrlServiceProvider implements ServiceModifierInterface {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    $service = $container->getDefinition('http_middleware.page_cache');

    // Stop if 'http_middleware.page_cache' has been tampered with.
    $core_middleware_class = 'Drupal\page_cache\StackMiddleware\PageCache';
    $core_implementation_used = $service->getClass() == $core_middleware_class;
    if (!$core_implementation_used) {
      assert($core_implementation_used, "The 'http_middleware.page_cache' service points to '$core_middleware_class'.");
      return;
    }

    // Replace the class and inject the config factory and database.
    $service->setClass('Drupal\purge_queuer_url\StackMiddleware\PageCacheUrlRegistrationWrapper');
    $service->addArgument(new Reference('purge_queuer_url.registry'));
    $service->addArgument(new Reference('config.factory'));
  }

}
