# Data Migrate Tool - Structure Export

This project contains Drush command(s) that export the site structure of a D7 or D8 website to CSV files.
Those CSV files can then be used to build mappings for a website migration.

The `dmt-structure-export` Drush command will generate several CSV files:
- `entity_bundles.csv`: All entity types and bundles (+ several settings)
- `entity_properties.csv`: All entity properties for each entity type and bundle
- `fields.csv`: All field bases
- `modules.csv`: The list of modules
- `taxonomy_terms.csv`: All taxonomy terms (with language_none/und or EN)

# Install
You can install this Drush tool:
1. Globally (in your `~/.drush` folder)
2. Or per drupal instance (in DRUPAL_ROOT/drush or `sites/*` like any other module)

The recommended way is to use composer.
- If you are using [Drupal Composer Project](https://github.com/drupal-composer/drupal-project)
    ```bash
    composer require vever001/dmt_structure_export:7.x-dev
    ```

- By adding and using `composer/installers`:
    ```bash
    cd DRUPAL_ROOT
    composer require composer/installers
    composer require vever001/dmt_structure_export:7.x-dev
    ```

- Or by getting the project files manually in `~/.drush` OR `DRUPAL_ROOT/drush`.

    ```bash
    git clone --branch 7.x git@github.com:vever001/dmt_structure_export.git
    cd dmt_structure_export
    composer install --no-dev
    ```
