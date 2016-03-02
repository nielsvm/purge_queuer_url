[//]: # ( clear&&curl -s -F input_files[]=@PROJECTPAGE.md -F from=markdown -F to=html http://c.docverter.com/convert|tail -n+11|head -n-2 )
[//]: # ( curl -s -F input_files[]=@PROJECTPAGE.md -F from=markdown -F to=pdf http://c.docverter.com/convert>PROJECTPAGE.pdf )

This module queues URLs or paths to your [Purge](https://www.drupal.org/project/purge) queue.

Drupal 8 introduces tag-based cache invalidation which is much more efficient than legacy URL or path invalidation. However, external caching platforms and CDNs that don't support this kind of cache invalidation are unfortunately unable to work with it. This module exists to fill in that gap and **should only be used when tag-based validation is no option**.

When Drupal adds pages to its page cache, the ``purge_queuer_url`` module will collect these URLs and their tags and build a database with it. Every time Drupal invalidates one or more cache tags, the URLs (or paths if desired) will be matched against the database and will get added to Purge's queue so that purgers supporting it will process them.

This also means that your site needs to get traffic - for instance by spidering it - before it starts to work effectively.