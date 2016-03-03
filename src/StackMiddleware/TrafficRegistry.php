<?php

/**
 * @file
 * Contains \Drupal\purge_queuer_url\StackMiddleware\TrafficRegistry.
 */

namespace Drupal\purge_queuer_url\StackMiddleware;

use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Query\Condition;
use Drupal\purge_queuer_url\StackMiddleware\TrafficRegistryInterface;

/**
 * Provides a database-driven traffic registry with URLs and tags.
 */
class TrafficRegistry extends ServiceProviderBase implements TrafficRegistryInterface {

  /**
   * The active database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Constructs a TrafficRegistry object.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The active database connection.
   */
  public function __construct(Connection $connection) {
    $this->connection = $connection;
  }

  /**
   * {@inheritdoc}
   */
  public function add($url_or_path, array $tags) {
    if (empty($tags) || empty($url_or_path)) {
      throw new \LogicException('Either $url_or_path or $path is left empty!');
    }

    // Build a list of tag IDs by adding and or selecting them from the db.
    $tag_ids = ';' . implode(';', array_keys($this->getTagIds($tags)));

    // Insert or update the URL with the shortened list of tag ids.
    $fields = ['url' => $url_or_path, 'tag_ids' => $tag_ids];
    $this->connection->merge('purge_queuer_url')
      ->insertFields(['url' => $url_or_path, 'tag_ids' => $tag_ids])
      ->updateFields(['url' => $url_or_path, 'tag_ids' => $tag_ids])
      ->key(['url' => $url_or_path])
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function clear() {
    $this->connection->delete('purge_queuer_url')->execute();
    $this->connection->delete('purge_queuer_url_tag')->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function getUrls(array $tags) {
    if (empty($tags)) {
      throw new \LogicException('$tags cannot be empty!');
    }

    // Retrieve tag IDs but without adding new ones.
    $tag_ids = array_keys($this->getTagIds($tags, FALSE));

    // Don't return any URLs when no tags actually exist.
    if (empty($tag_ids)) {
      return [];
    }

    // Build a OR condition with LIKES on tag_ids for every tag.
    $or = new Condition('OR');
    foreach ($tag_ids as $tag_id) {
      $syntax = '%;' . $this->connection->escapeLike($tag_id) . '%';
      $or->condition('tag_ids', $syntax, 'LIKE');
    }

    // Perform the query and fetch the URLs from its resultset.
    $urls = [];
    $results = db_select('purge_queuer_url', 'u')
      ->fields('u', ['url'])
      ->condition($or)
      ->execute();
    foreach ($results as $url) {
      $urls[] = $url->url;
    }

    return $urls;
  }

  /**
   * Retrieve database IDs for the given set of tags or add missing.
   *
   * @param string[] $tags
   *   Unassociative list of cache tags.
   * @param bool $add_missing
   *   Add tags that are missing to the database.
   *
   * @throws \LogicException
   *   Thrown when $tags is left empty.
   *
   * @return string[]
   *   Associative array with ID as key and the tag as value.
   */
  protected function getTagIds(array $tags, $add_missing = TRUE) {
    if (empty($tags)) {
      throw new \LogicException('$path cannot be empty!');
    }

    // Define the closure that queries existing tags from the database.
    $load_from_db = function(&$tags, &$ids) {
      $db_results = $this->connection->select('purge_queuer_url_tag', 't')
      ->fields('t', ['tagid', 'tag'])
      ->condition('tag', $tags, 'IN')
      ->execute();
      foreach ($db_results as $tag) {
        $ids[intval($tag->tagid)] = $tag->tag;
        unset($tags[array_search($tag->tag, $tags)]);
      }
    };

    // First attempt to load everything from the database.
    $ids = [];
    $load_from_db($tags, $ids);

    // When given tags don't exist, they're left in $tags.
    // Missing tags are left in $tags, add them to the database if needed.
    if (count($tags) && $add_missing) {
      $q = $this->connection->insert('purge_queuer_url_tag')->fields(['tag']);
      foreach ($tags as $tag) {
        $q->values(['tag' => $tag]);
      }
      $q->execute();
      $load_from_db($tags, $ids);
    }

    return $ids;
  }

}
