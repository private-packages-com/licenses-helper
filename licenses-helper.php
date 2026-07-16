<?php

namespace PrivatePackages\DiscoverLicensesCommand;

/**
 * Plugin Name: Private Packages: Licenses Helper
 * Description: A WP-CLI command to discover licenses for installed premium plugins. Easily import them into your Private Packages repository.
 * Version: 0.1.0
 * Author: Tom Broucke
 * Author URI: https://private-packages.com
 * License: MIT
 * License URI: https://opensource.org/licenses/MIT
 * Text Domain: discover-licenses-command
 * Requires PHP: 8.0
 */
if (file_exists(__DIR__.'/vendor/autoload.php')) {
    require_once __DIR__.'/vendor/autoload.php';
}

if (defined('WP_CLI')) {
    require_once __DIR__.'/cli-command.php';
}

add_action('admin_menu', function () {
    if (! current_user_can('manage_options')) {
        return;
    }

    (new AdminPage(new LicenseDiscoverer))->addMenuPage();
});
