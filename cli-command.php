<?php

use PrivatePackages\DiscoverLicensesCommand\DiscoverLicensesCommand;

if (class_exists('WP_CLI')) {
    WP_CLI::add_command('private-packages discover-licenses', DiscoverLicensesCommand::class);
}
