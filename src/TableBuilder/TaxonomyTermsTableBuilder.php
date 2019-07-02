<?php

namespace Drush\dmt_structure_export\TableBuilder;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * TaxonomyTermsTableBuilder class.
 */
class TaxonomyTermsTableBuilder extends TableBuilder {

  /**
   * TaxonomyTermsTableBuilder constructor.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The service container.
   */
  public function __construct(ContainerInterface $container) {
    parent::__construct($container);

    $this->header = [
      'machine_name' => dt('Vocabulary'),
      'tid' => dt('Term ID'),
      'name' => dt('Term name'),
      'term_description' => dt('Term description'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container
    );
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
