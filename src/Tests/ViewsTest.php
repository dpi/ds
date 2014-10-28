<?php

/**
 * @file
 * Definition of Drupal\ds\Tests\ViewsTest.
 */

namespace Drupal\ds\Tests;
use Drupal\views\Tests\ViewTestData;
use Drupal\views\ViewExecutable;

/**
 * Tests for Display Suite Views integration.
 *
 * @group display_suite
 */
class ViewsTest extends BaseTest {

  /**
   * Views used by this test.
   *
   * @var array
   */
  public static $testViews = array('ds-testing');

  protected function setUp() {
    parent::setUp();

    // Ensure that the plugin definitions are cleared.
    foreach (ViewExecutable::getPluginTypes() as $plugin_type) {
      $this->container->get("plugin.manager.views.$plugin_type")->clearCachedDefinitions();
    }

    ViewTestData::createTestViews(get_class($this), array('ds_test'));
  }

  /**
   * Test views integration.
   */
  function testDSViews() {

    $edit_tag_1 = array(
      'field_tags' => 'Tag 1',
    );
    $edit_tag_2 = array(
      'field_tags' => 'Tag 2',
    );

    // Create 3 nodes.
    $settings_1 = array(
      'type' => 'article',
      'title' => 'Article 1',
      'created' => REQUEST_TIME,
    );
    $node_1 = $this->drupalCreateNode($settings_1);
    $this->drupalPostForm('node/' . $node_1->id() . '/edit', $edit_tag_1, t('Save and keep published'));
    $settings_2 = array(
      'type' => 'article',
      'title' => 'Article 2',
      'created' => REQUEST_TIME + 3600,
    );
    $node_2 = $this->drupalCreateNode($settings_2);
    $this->drupalPostForm('node/' . $node_2->id() . '/edit', $edit_tag_1, t('Save and keep published'));
    $settings_3 = array(
      'type' => 'article',
      'title' => 'Article 3',
      'created' => REQUEST_TIME + 7200,
    );
    $node_3 = $this->drupalCreateNode($settings_3);
    $this->drupalPostForm('node/' . $node_3->id() . '/edit', $edit_tag_2, t('Save and keep published'));

    // Configure teaser and full layout.
    $layout = array(
      'layout' => 'ds_2col',
    );
    $fields = array(
      'fields[node_title][region]' => 'left',
      'fields[body][region]' => 'right',
    );
    $assert = array(
      'regions' => array(
        'left' => '<td colspan="8">' . t('Left') . '</td>',
        'right' => '<td colspan="8">' . t('Right') . '</td>',
      ),
    );
    $this->dsSelectLayout($layout, $assert, 'admin/structure/types/manage/article/display/teaser');
    $this->dsConfigureUI($fields, 'admin/structure/types/manage/article/display/teaser');
    $layout = array(
      'layout' => 'ds_4col',
    );
    $fields = array(
      'fields[node_post_date][region]' => 'first',
      'fields[body][region]' => 'second',
      'fields[node_author][region]' => 'third',
      'fields[node_links][region]' => 'fourth',
    );
    $assert = array(
      'regions' => array(
        'first' => '<td colspan="8">' . t('First') . '</td>',
        'second' => '<td colspan="8">' . t('Second') . '</td>',
        'third' => '<td colspan="8">' . t('Third') . '</td>',
        'fourth' => '<td colspan="8">' . t('Fourth') . '</td>',
      ),
    );
    $this->dsSelectLayout($layout, $assert);
    $this->dsConfigureUI($fields);

    // Get default teaser view.
    $this->drupalGet('ds-testing');
    foreach (array('group-left', 'group-right') as $region) {
      $this->assertRaw($region, t('Region !region found', array('!region' => $region)));
    }
    $this->assertRaw('Article 1');
    $this->assertRaw('Article 2');
    $this->assertRaw('Article 3');

    // Get alternating view.
    $this->drupalGet('ds-testing-2');
    foreach (array('group-left', 'group-right', 'first', 'second', 'third', 'fourth') as $region) {
      $this->assertRaw($region, t('Region !region found', array('!region' => $region)));
    }
    $this->assertNoRaw('Article 1');
    $this->assertRaw('Article 2');
    $this->assertRaw('Article 3');

    // Get grouping view (without changing header function).
    $this->drupalGet('ds-testing-3');
    foreach (array('group-left', 'group-right') as $region) {
      $this->assertRaw($region, t('Region !region found', array('!region' => $region)));
    }
    $this->assertRaw('Article 1');
    $this->assertRaw('Article 2');
    $this->assertRaw('Article 3');
    $this->assertRaw('<h2 class="grouping-title">1</h2>');
    $this->assertRaw('<h2 class="grouping-title">2</h2>');

    // Get grouping view (with changing header function).
    $this->drupalGet('ds-testing-4');
    foreach (array('group-left', 'group-right') as $region) {
      $this->assertRaw($region, t('Region !region found', array('!region' => $region)));
    }
    $this->assertRaw('Article 1');
    $this->assertRaw('Article 2');
    $this->assertRaw('Article 3');
    $this->assertRaw('<h2 class="grouping-title">Tag 1</h2>');
    $this->assertRaw('<h2 class="grouping-title">Tag 2</h2>');

    // Get advanced function view.
    $this->drupalGet('ds-testing-5');
    $this->assertRaw('Advanced display for id 1');
    $this->assertRaw('Advanced display for id 2');
    $this->assertRaw('Advanced display for id 3');

    // Test views templates overrides.
    $this->drupalGet('admin/structure/ds/vd/manage/ds_testing-page_5/display');
    $this->assertText('No view found to layout.');
    $edit = array(
      'vd' => 'ds_testing-page_5',
    );
    $this->drupalPostForm('admin/structure/ds/vd', $edit, t('Add'));
    $this->dsSelectLayout(array(), array(), 'admin/structure/ds/vd/manage/ds_testing-page_5/display');
    $edit = array(
      'fields[pager][region]' => 'header',
      'fields[footer][region]' => 'header',
      'fields[rows][region]' => 'left',
      'fields[exposed][region]' => 'right',
      'fields[header][region]' => 'footer',
    );
    $this->dsConfigureUI($edit, 'admin/structure/ds/vd/manage/ds_testing-page_5/display');

    // Test on the views page itself.
    $this->drupalGet('ds-testing-6');
    $this->assertRaw('<div class="group-header">
    <p>Footer text</p>
  </div>');
    $this->assertRaw('<div class="group-left">
      <div class="views-row views-row-1 views-row-odd views-row-first">');
    $this->assertRaw('<div class="group-right">
    <form action="ds-testing-6"');
    $this->assertRaw('<div class="group-footer">
    <p>Header text</p>');
  }
}
