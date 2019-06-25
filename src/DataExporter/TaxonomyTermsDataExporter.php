<?php

namespace Drush\dmt_structure_export\DataExporter;

/**
 * TaxonomyTermsDataExporter class.
 */
class TaxonomyTermsDataExporter extends DataExporter implements DataExporterInterface {

  /**
   * TaxonomyTermsDataExporter constructor.
   */
  public function __construct() {
    $this->header = array(
      'machine_name' => dt('Vocabulary'),
      'tid' => dt('Term ID'),
      'name' => dt('Term name'),
      'term_description' => dt('Term description'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function process() {
    $query = db_select('taxonomy_term_data', 'term', array('fetch' => \PDO::FETCH_ASSOC));
    $query->innerJoin('taxonomy_vocabulary', 'voc', 'term.vid = voc.vid');
    $query->fields('voc', array('machine_name'));
    $query->fields('term', array('tid', 'name'));
    $query->addExpression('SUBSTRING(term.description, 1, 100)', 'term_description');
    $query->condition('term.language', array('und', 'en'), 'IN');
    $query->orderby('term.vid');
    $query->orderby('term.name');
    $this->setRows($query->execute());
  }

}
