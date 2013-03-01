<?php

/**
 * @file
 * Contains \Drupal\ds\Plugin\ds\field\BookNavigation.
 */

namespace Drupal\ds\Plugin\ds\field;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Annotation\Plugin;

/**
 * Function field that renders the title of a node
 *
 * @Plugin(
 *   id = "book_navigation",
 *   title = @Translation("Book navigation"),
 *   entity_type = "node",
 *   module = "ds"
 * )
 */
class BookNavigation extends PluginBase {

  /**
   * Implements \Drupal\ds\Plugin\ds\field\PluginBase::displays().
   */
  public function displays() {

    // Get all the allowed types
    $types = config('book.settings')->get('allowed_types');

    $displays = array();
    foreach ($types as $type) {
       $displays[] = $type . '|full';
    }

    // When there are no displays, never render this field.
    if (empty($displays)) {
      return FALSE;
    }
    else {
      return $displays;
    }

    return $displays;
  }

}
