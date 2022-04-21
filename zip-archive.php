<?php

/**
 * Plugin Name: Import WP - Zip Archive Importer Addon
 * Plugin URI: https://www.importwp.com
 * Description: Allow Import WP to import zip files containing xml and csv files.
 * Author: James Collings <james@jclabs.co.uk>
 * Version: 0.1.1 
 * Author URI: https://www.importwp.com
 * Network: True
 */

add_action('admin_init', 'iwp_zip_archive_check');

function iwp_zip_archive_requirements_met()
{
    return false === (is_admin() && current_user_can('activate_plugins') &&  (!class_exists('ZipArchive') || (!function_exists('import_wp_pro') && !function_exists('import_wp')) || version_compare(IWP_VERSION, '2.4.6', '<')));
}

function iwp_zip_archive_check()
{
    if (!iwp_zip_archive_requirements_met()) {

        add_action('admin_notices', 'iwp_zip_archive_notice');

        deactivate_plugins(plugin_basename(__FILE__));

        if (isset($_GET['activate'])) {
            unset($_GET['activate']);
        }
    }
}

function iwp_zip_archive_setup()
{
    if (!iwp_zip_archive_requirements_met()) {
        return;
    }

    $base_path = dirname(__FILE__);

    require_once $base_path . '/setup.php';
}
add_action('plugins_loaded', 'iwp_zip_archive_setup', 9);

function iwp_zip_archive_notice()
{
    echo '<div class="error">';
    echo '<p><strong>Import WP - Zip Archive Importer Addon</strong> requires that you have <strong>Import WP v2.4.6 or newer</strong> installed, and php ZipArchive extension installed.</p>';
    echo '</div>';
}
