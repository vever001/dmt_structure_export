# Data Migrate Tool - Structure Export

This project contains Drush command(s) to export a Drupal 7 or Drupal 8 website structure to CSV files.
Those CSV files can then be used to build mappings for a website migration.

The `dmt-se:export` command can be used to generate a single export.

The `dmt-se:export-all` command will run all exports and generate CSV files:
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

The recommended way is to use Composer.

You can install this Drush tool:

#### Per drupal instance (recommended)

The dmt_structure_export folder should go in `DRUPAL_ROOT/drush/Commands` or `DRUPAL_ROOT/../drush/Commands`
or `DRUPAL_ROOT/sites/all/drush/Commands`.

Using composer/installers: `composer require composer/installers`

Make sure you have the following in your composer.json's "extra" section:

    "installer-paths": {
        ...
        "drush/Commands/{$name}": ["type:drupal-drush"]
    }
    

Finally: `composer require ioannis-ampatzis/dmt_structure_export:8.x-1.x-dev`

#### Or globally

You can install it in your `~/.drush` folder.

  * Create a `drush-extensions/Commands` folder in `~/.drush`
  * Copy the [example.drushrc.php file](https://github.com/drush-ops/drush/blob/8.x/examples/example.drushrc.php) to `~/.drush` and rename it to `drushrc.php`
  * Add and adapt the following:
    * `$options['include'] = array('/path/to/drush-extensions');`
  * From `drush-extensions/Commands` run

     ```bash
     git clone --branch 7.x-1.x https://github.com/ioannis-ampatzis/dmt_structure_export.git
     cd dmt_structure_export
     composer install --no-dev
     ```
