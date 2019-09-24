<?php

namespace Drush\dmt_structure_export\TableBuilder;

/**
 * TaxonomyTermsTableBuilder class.
 */
class TaxonomyTermsTableBuilder extends TableBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header = [
      'machine_name' => dt('Vocabulary'),
      'tid' => dt('Term ID'),
      'name' => dt('Term name'),
      'term_description' => dt('Term description'),
    ];

    $this->setHeader($header);
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
    $this->sanitizeDescriptions($rows);

    $this->setRows($rows);
  }

  /**
   * Sanitizes all term descriptions.
   *
   * @param $rows
   *   The rows of taxonomy terms.
   */
  protected function sanitizeDescriptions(&$rows) {
    foreach ($rows as &$row) {
      $row['term_description'] = $this->sanitizeDescription(trim($row['term_description']));
    }
  }

  /**
   * Sanitizes a single term description.
   *
   * @param $string
   *   The string to sanitize.
   *
   * @return string|string[]|null
   *   The sanitized string or null.
   */
  protected function sanitizeDescription($string) {
    $patterns = ['/"/', '/\r?\n/'];
    $replacements = ['""', '<br>'];

    return preg_replace($patterns, $replacements, $string);
  }

}
