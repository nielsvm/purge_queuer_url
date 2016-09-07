<?php

namespace Drupal\purge_queuer_url\StackMiddleware;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\PageCache\RequestPolicyInterface;
use Drupal\Core\PageCache\ResponsePolicyInterface;
use Drupal\page_cache\StackMiddleware\PageCache;
use Drupal\purge_queuer_url\StackMiddleware\TrafficRegistryInterface;

/**
 * Collects URLs for page cache misses.
 */
class PageCacheUrlRegistrationWrapper extends PageCache implements HttpKernelInterface {

  /**
   * The traffic registry with the stored URLs and tags.
   *
   * @var \Drupal\purge_queuer_url\TrafficRegistryInterface
   */
  protected $registry;

  /**
   * Whether to override the hostname (string value) or keep as is (false).
   *
   * @var false|string
   */
  protected $host = FALSE;

  /**
   * Whether to override the scheme (string value) or keep as is (false).
   *
   * @var false|string
   */
  protected $scheme = FALSE;

  /**
   * Whether to queue paths (true) instead of URLs or not (false).
   *
   * @var true|false
   */
  protected $queue_paths = NULL;

  /**
   * Constructs a PageCacheUrlRegistrationWrapper object.
   *
   * @param \Symfony\Component\HttpKernel\HttpKernelInterface $http_kernel
   *   The decorated kernel.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache
   *   The cache bin.
   * @param \Drupal\Core\PageCache\RequestPolicyInterface $request_policy
   *   A policy rule determining the cacheability of a request.
   * @param \Drupal\Core\PageCache\ResponsePolicyInterface $response_policy
   *   A policy rule determining the cacheability of the response.
   * @param \Drupal\purge_queuer_url\TrafficRegistryInterface $registry
   *   The traffic registry with the stored URLs and tags.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   */
  public function __construct(HttpKernelInterface $http_kernel, CacheBackendInterface $cache, RequestPolicyInterface $request_policy, ResponsePolicyInterface $response_policy, TrafficRegistryInterface $registry, ConfigFactoryInterface $config_factory) {
    parent::__construct($http_kernel, $cache, $request_policy, $response_policy);
    $this->registry = $registry;

    // Take the configured settings from our configuration object.
    $settings = $config_factory->get('purge_queuer_url.settings');
    $this->queue_paths = $settings->get('queue_paths');
    if ($settings->get('host_override')) {
      $this->host = $settings->get('host');
    }
    if ($settings->get('scheme_override')) {
      $this->scheme = $settings->get('scheme');
    }
  }

  /**
   * Generates the URL or path to register.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   A request object.
   *
   * @return string
   */
  protected function generateUrlOrPathToRegister(Request $request) {
    if (NULL !== $qs = $request->getQueryString()) {
      $qs = '?' . $qs;
    }
    $scheme = ($this->scheme == FALSE) ? $request->getScheme() : $this->scheme;
    $host = ($this->host == FALSE) ? $request->getHttpHost() : $this->host;
    $path = $request->getBaseUrl() . $request->getPathInfo() . $qs;

    if ($this->queue_paths) {
      return ltrim($path, '/');
    }
    else {
      return $scheme .'://'. $host . $path;
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function set(Request $request, Response $response, $expire, array $tags) {
    parent::set($request, $response, $expire, $tags);

    // Do not gather URLs without any tags.
    if (!count($tags)) {
      return;
    }

    // Only collect URLs that set a max-age value that will externally cached.
    if (!$response->getMaxAge()) {
      return;
    }

    // Prevent entries like '/node/1/delete' and for instance forbidden paths.
    if ($response->getStatusCode() !== 200) {
      return;
    }

    $this->registry->add($this->generateUrlOrPathToRegister($request), $tags);
  }

}
