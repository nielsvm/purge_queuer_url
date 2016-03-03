<?php

/**
 * @file
 * Contains \Drupal\purge_queuer_url\StackMiddleware\TrafficRegistry.
 */

namespace Drupal\purge_queuer_url\StackMiddleware;

use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Drupal\Core\Database\Connection;
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

    // Retrieve and/or add tags to the database and get its keys.
    $tags = $this->getTagIds($tags);

    // Insert the URL or just retrieve the urlid its associated with.
    $upsert = $this->connection->merge('purge_queuer_url_url')
      ->insertFields(['url' => $url_or_path])
      ->key(['url' => $url_or_path])
      ->execute();
    $urlid = (int) $this->connection->select('purge_queuer_url_url', 'u')
      ->fields('u', ['urlid'])
      ->condition('url', $url_or_path)
      ->execute()
      ->fetchField(0);

    // Delete existing url->tags associations.
    if (is_null($upsert)) {
      $this->connection->delete('purge_queuer_url_urltag')
      ->condition('urlid', $urlid)
      ->execute();
    }

    // Add url->tags associations.
    $insert = $this->connection->insert('purge_queuer_url_urltag')
      ->fields(['urlid', 'tagid']);
    foreach ($tags as $tagid => $tag) {
      $insert->values(['urlid' => $urlid, 'tagid' => $tagid]);
    }
    $insert->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function clear() {
    $this->connection->delete('purge_queuer_url_tag')->execute();
    $this->connection->delete('purge_queuer_url_url')->execute();
    $this->connection->delete('purge_queuer_url_urltag')->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function getUrls(array $tags) {
    if (empty($tags)) {
      throw new \LogicException('$tags cannot be empty!');
    }

    // Retrieve tag IDs but without adding new ones.
    $tags = array_keys($this->getTagIds($tags, FALSE));

    // Don't return any URLs when no tags actually exist.
    if (empty($tags)) {
      return [];
    }

    // Perform a join and fetch the URLs.
    $query = db_select('purge_queuer_url_url', 'u')->distinct();
    $query->join('purge_queuer_url_urltag', 'ut', 'u.urlid = ut.urlid');
    $query->condition('ut.tagid', $tags, 'IN')->fields('u', ['url']);
    $result = $query->execute();

    // Put the results in a non-associative array.
    $urls = [];
    foreach ($result as $url) {
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
