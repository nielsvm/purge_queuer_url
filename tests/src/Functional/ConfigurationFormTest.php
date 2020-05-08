<?php

namespace Drupal\Tests\purge_queuer_url\Functional;

use Drupal\Tests\purge_ui\Functional\QueuerConfigFormTestBase;

/**
 * Tests \Drupal\purge_queuer_url\Form\ConfigurationForm.
 *
 * @group purge
 */
class ConfigurationFormTest extends QueuerConfigFormTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['purge_queuer_url'];

  /**
   * The plugin ID for which the form tested is rendered for.
   *
   * @var string
   */
  protected $plugin = 'urlpath';

  /**
   * The full class of the form being tested.
   *
   * @var string
   */
  protected $formClass = 'Drupal\purge_queuer_url\Form\ConfigurationForm';

  /**
   * Test the blacklist section.
   *
   * @TODO add tests for the blacklist.
   */
  public function testFieldExistence() {
    $this->drupalLogin($this->adminUser);
    $this->drupalGet($this->route);
    // Assert the standard fields and their default values.
    $this->assertSession()->fieldExists('edit-queue-paths');
    $this->assertSession()->checkboxNotChecked('edit-queue-paths');
    $this->assertSession()->fieldExists('edit-host-override');
    $this->assertSession()->checkboxNotChecked('edit-host-override');
    $this->assertSession()->fieldExists('edit-host');
    $this->assertSession()->fieldValueEquals('edit-host', '');
    $this->assertSession()->fieldExists('edit-scheme-override');
    $this->assertSession()->checkboxNotChecked('edit-scheme-override');
    $this->assertSession()->fieldExists('edit-scheme');
    $this->assertSession()->fieldValueEquals('edit-scheme', 'http');
    $this->assertRaw('Clear traffic history');
    // Test that direct configuration changes are reflected properly.
    $this->config('purge_queuer_url.settings')
      ->set('queue_paths', TRUE)
      ->set('host_override', TRUE)
      ->set('host', 'foobar.baz')
      ->set('scheme_override', TRUE)
      ->set('scheme', 'https')
      ->save();
    $this->drupalGet($this->route);
    $this->assertSession()->checkboxChecked('edit-queue-paths');
    $this->assertSession()->checkboxChecked('edit-host-override');
    $this->assertSession()->fieldValueEquals('edit-host', 'foobar.baz');
    $this->assertSession()->checkboxChecked('edit-scheme-override');
    $this->assertSession()->fieldValueEquals('edit-scheme', 'https');
  }

}
