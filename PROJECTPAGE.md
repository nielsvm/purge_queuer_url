[//]: # ( clear&&curl -s -F input_files[]=@PROJECTPAGE.md -F from=markdown -F to=html http://c.docverter.com/convert|tail -n+11|head -n-2 )
[//]: # ( curl -s -F input_files[]=@PROJECTPAGE.md -F from=markdown -F to=pdf http://c.docverter.com/convert>PROJECTPAGE.pdf )

This module queues URLs or paths to your [Purge](https://www.drupal.org/project/purge) queue.

Drupal 8 introduces tag-based cache invalidation which is much more efficient than legacy URL or path based invalidation and leads to less processing and smaller queues. However, when your CDN or external caching system **does not support tag-based invalidation**, this module is for you!

#### How it works
The module trains its _traffic registry_ by collecting URLs from requests that miss cache inside Drupal's own page cache. These URLs are stored along with their associated _cache tags_ inside this same _traffic registry_. Now when Drupal invalidates a set of tags because someone edited a page, these tags are used to fetch URLs from the registry and are added to the Purge queue.

###### Accuracy
Those that remember the [cache expiration](https://www.drupal.org/project/expire) module for Drupal 7 are familiar with the _side effects_ of pages not being detected automatically, like views. This modules guarantees 99,99% accurate cache invalidation since it leverages Drupal 8's much innovated architecture, which is widely covered by many tests and covers edge cases most site builders wouldn't even think about. Examples of things that _just work_ with this are pagers (``/news/?page=10``), RSS or Json views (``/rss.xml``) and that one dynamic block placed on ``/contact`` that shows the company news.

#### Getting started

* Setup the [Purge module](https://www.drupal.org/project/purge).
* Make sure that your purger(s) support URL or path invalidations.
* ``drush en purge_queuer_url --yes``
* ``drush cset system.performance cache.page.max_age 31536000`` (year)
* ``drush cache-rebuild`` to empty Drupal's page cache.

To achieve accurate URL based cache invalidation, it is best to take a head start by training which _traffic registry_ that ``purge_queuer_url`` maintains:

```
wget -r -nd --delete-after -l100 --spider http://mydrupalsite/
```
When this process finished, editing any item on your site should result in several URLs being added to the Purge queue (see ``drush p-queue-browse``). Ordinary website traffic will automatically add new URLs to your _traffic registry_ and won't require any further maintenance.
