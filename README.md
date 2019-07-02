# Data Migrate Tool - Structure Export

This project contains Drush command(s) to export a Drupal 7 or Drupal 8 website structure to CSV files.
Those CSV files can then be used to build mappings for a website migration.

The `dmt-se:export` command can be used to generate a single export.

The `dmt-se:export-all` command will run all exports and generate CSV files:
- `entity_bundles.csv`: All entity types and bundles (+ several settings)
- `entity_properties.csv`: All entity properties for each entity type and bundle
- `fields.csv`: All field bases
- `modules.csv`: The list of modules

# Requirements

* PHP 7.1 or higher
* Drush 8.2 / 9.0 or higher

# Installation

The recommended way is to use Composer.

You can install this Drush tool:

1\. Per drupal instance (in `DRUPAL_ROOT/drush` or `DRUPAL_ROOT/../drush` or `DRUPAL_ROOT/sites/all/drush`)

    ```bash
    composer require composer/installers
    composer require vever001/dmt_structure_export:8.x-1.x-dev
    ```

2\. Or globally (in your `~/.drush` folder)

  * Create a `drush-extensions/Commands` folder in `~/.drush`
  * Copy the [example.drush.yml file](https://github.com/drush-ops/drush/blob/master/examples/example.drush.yml) to `~/.drush` and rename it to `drushrc.yml`
  * Add and adapt the following:
    * ```
      drush:
        paths:
          include:
            - '${env.home}/path/to/drush-extensions'
      ```
  * From `drush-extensions/Commands` run

     ```bash
     git clone --branch 8.x-1.x https://github.com/vever001/dmt_structure_export.git
     cd dmt_structure_export
     composer install --no-dev
     ```
