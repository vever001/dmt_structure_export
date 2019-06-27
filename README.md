# Data Migrate Tool - Structure Export

This project contains Drush command(s) to export a Drupal 7 or Drupal 8 website structure to CSV files.
Those CSV files can then be used to build mappings for a website migration.

The `dmt-structure-export` Drush command will generate several CSV files:
- `entity_bundles.csv`: All entity types and bundles (+ several settings)
- `entity_properties.csv`: All entity properties for each entity type and bundle
- `fields.csv`: All field bases
- `modules.csv`: The list of modules
- `taxonomy_terms.csv`: All taxonomy terms (with language_none/und or EN)

# Requirements

* PHP 5.6 or higher
* Drush 8.1.18 or higher is required:
  *  this tool uses [Consolidation\AnnotatedCommand](https://github.com/consolidation/annotated-command) and [Consolidation\OutputFormatters](https://github.com/consolidation/output-formatters) 

# Installation

You can install this Drush tool:

1\. Per drupal instance in

* `DRUPAL_ROOT/drush`
* or `DRUPAL_ROOT/../drush`
* or `DRUPAL_ROOT/sites/all/drush`

2\. Or globally (in your `~/.drush` folder)

  * Create a `drush-extensions/Commands` folder in `~/.drush`
  * Copy the [example.drushrc.php file](https://github.com/drush-ops/drush/blob/8.x/examples/example.drushrc.php) to `~/.drush` and rename it to `drushrc.php`
  * Add and adapt the following:
    * `$options['include'] = array('/path/to/drush-extensions');`
  * Place this project in `drush-extensions/Commands`
    * so you have `drush-extensions/Commands/dmt_structure_export/DmtStructureExportCommands.php`
