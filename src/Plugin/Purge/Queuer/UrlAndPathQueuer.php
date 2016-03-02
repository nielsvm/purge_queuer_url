<?php

 /**
  * @file
  * Contains \Drupal\purge_queuer_url\Plugin\Purge\Queuer\UrlAndPathQueuer.
  */

namespace Drupal\purge_queuer_url\Plugin\Purge\Queuer;

use Drupal\purge\Plugin\Purge\Queuer\QueuerInterface;
use Drupal\purge\Plugin\Purge\Queuer\QueuerBase;

/**
 * Queues URLs or paths to your Purge queue after building a URL database.
 *
 * @PurgeQueuer(
 *   id = "urlpath",
 *   label = @Translation("URL and path queuer"),
 *   description = @Translation("Queues URLs or paths to your Purge queue after building a URL database."),
 *   enable_by_default = true,
 *   configform = "\Drupal\purge_queuer_url\Form\ConfigurationForm",
 * )
 */
class UrlAndPathQueuer extends QueuerBase implements QueuerInterface {

}
