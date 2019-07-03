<?php

namespace Drush\dmt_structure_export\TableBuilder;

/**
 * TaxonomyTermsTableBuilder class.
 */
class TaxonomyTermsTableBuilder extends TableBuilder {

  /**
   * {@inheritdoc}
   */
  protected function buildHeader() {
    $this->header = [
      'machine_name' => dt('Vocabulary'),
      'tid' => dt('Term ID'),
      'name' => dt('Term name'),
      'term_description' => dt('Term description'),
    ];
    return $this->header;
  }

  /**
   * {@inheritdoc}
   */
  public function buildRows() {
    $query = db_select('taxonomy_term_data', 'term', ['fetch' => \PDO::FETCH_ASSOC]);
    $query->innerJoin('taxonomy_vocabulary', 'voc', 'term.vid = voc.vid');
    $query->fields('voc', ['machine_name']);
    $query->fields('term', ['tid', 'name']);
    $query->addExpression('SUBSTRING(term.description, 1, 100)', 'term_description');
    $query->condition('term.language', ['und', 'en'], 'IN');
    $query->orderby('term.vid');
    $query->orderby('term.name');
    $rows = $query->execute()->fetchAllAssoc('tid', \PDO::FETCH_ASSOC);
    $this->rows = $rows;
  }

}
