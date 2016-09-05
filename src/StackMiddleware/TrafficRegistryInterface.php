<?php

namespace Drupal\purge_queuer_url\StackMiddleware;

use Drupal\Core\DependencyInjection\ServiceProviderInterface;
use Drupal\Core\DependencyInjection\ServiceModifierInterface;

/**
 * Describes a traffic registry with URLs and tags.
 */
interface TrafficRegistryInterface extends ServiceProviderInterface, ServiceModifierInterface {

  /**
   * Register a new URL or path with its associated cache tags at the registry.
   *
   * @warning
   *   Implementation specific contstraints - such as database field length -
   *   might dismiss the URL being added. Although implementations should
   *   prevent this from happening at all cost, it could happen.
   *
   * @param string $url_or_path
   *   The URL or path string to register (may already exist).
   * @param string[] $tags
   *   Unassociative array with cache tags associated with the URL or path.
   *
   * @throws \LogicException
   *   Thrown when $tags is empty.
   */
  public function add($url_or_path, array $tags);

  /**
   * Wipe out all gathered traffic information.
   */
  public function clear();

  /**
   * Collect URLs and paths associated with the given list of tags.
   *
   * @param string[] $tags
   *   Unassociative list of cache tags that belong to one or more URls/paths.
   *
   * @throws \LogicException
   *   Thrown when $tags is empty.
   *
   * @return string[]
   *   Returns an array with URLs/paths associated with the tags.
   */
  public function getUrls(array $tags);

}
