<?php

/**
 * @file
 * Contains \Drupal\purge_queuer_url\StackMiddleware\PageCacheUrlRegistrationWrapper.
 */

namespace Drupal\purge_queuer_url\StackMiddleware;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Drupal\Core\Database\Connection;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\PageCache\RequestPolicyInterface;
use Drupal\Core\PageCache\ResponsePolicyInterface;
use Drupal\page_cache\StackMiddleware\PageCache;

/**
 * Collects URLs for page cache misses.
 */
class PageCacheUrlRegistrationWrapper extends PageCache implements HttpKernelInterface {

  /**
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

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
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Database\Connection $database
   *   The active database connection.
   */
  public function __construct(HttpKernelInterface $http_kernel, CacheBackendInterface $cache, RequestPolicyInterface $request_policy, ResponsePolicyInterface $response_policy, ConfigFactoryInterface $config_factory, Connection $database) {
    parent::__construct($http_kernel, $cache, $request_policy, $response_policy);
    $this->database = $database;

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
      $qs = '?'.$qs;
    }
    $scheme = ($this->scheme == FALSE) ? $request->getScheme() : $this->scheme;
    $host = ($this->host == FALSE) ? $request->getHttpHost() : $this->host;
    $path = $request->getBaseUrl() . $request->getPathInfo() . $qs;

    if ($this->queue_paths) {
      return $path;
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
    $url_or_path = $this->generateUrlOrPathToRegister($request);
  }

}
