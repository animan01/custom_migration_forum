<?php

namespace Drupal\custom_migration_forum\Plugin\migrate\source;

use Drupal\migrate\Row;
use Drupal\migrate\Plugin\migrate\source\SqlBase;

/**
 * Drupal 7 taxonomy terms source from database.
 *
 * @MigrateSource(
 *   id = "custom_migration_forum_term",
 *   source_provider = "taxonomy"
 * )
 */
class Terms extends SqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->select('taxonomy_term_data', 'td')
      ->fields('td', ['tid', 'vid', 'name', 'description', 'weight', 'format'])
      ->fields('tv', ['vid', 'machine_name'])
      ->distinct();
    // Add table for condition on query.
    $query->innerJoin('taxonomy_vocabulary', 'tv', 'td.vid = tv.vid');
    // Filtered out unnecessary dictionaries.
    $query->condition('tv.machine_name', 'forums');
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'tid' => $this->t('The term ID.'),
      'vid' => $this->t('Existing term VID'),
      'name' => $this->t('The name of the term.'),
      'description' => $this->t('The term description.'),
      'weight' => $this->t('Weight'),
      'parent' => $this->t("The Drupal term IDs of the term's parents."),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    // Find parents for this row.
    $parents = $this->select('taxonomy_term_hierarchy', 'th')
      ->fields('th', ['parent', 'tid']);
    $parents->condition('tid', $row->getSourceProperty('tid'));
    $parents = $parents->execute()->fetchCol();
    $row->setSourceProperty('parent', reset($parents));
    return parent::prepareRow($row);
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['tid']['type'] = 'integer';
    return $ids;
  }

}
