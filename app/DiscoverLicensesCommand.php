<?php

namespace PrivatePackages\DiscoverLicensesCommand;

use PrivatePackages\DiscoverLicensesCommand\Enums\PackageStatus;

class DiscoverLicensesCommand
{
    private LicenseDiscoverer $discoverer;

    public function __construct()
    {
        $this->discoverer = new LicenseDiscoverer;
    }

    /**
     * Discover licenses for installed premium plugins.
     *
     * ## OPTIONS
     *
     * [--format=<format>]
     * : Output format. Options: json, table. Default: json.
     *
     * [--plugin=<slug>]
     * : Only return data for this plugin slug.
     *
     * [--filter=<string>]
     * : Only return plugins whose slug contains this string (e.g. woocommerce).
     *
     * [--recipe=<recipe>]
     * : Only return plugins that use this recipe (e.g. woo_commerce, edd).
     *
     * ## EXAMPLES
     *
     *     wp private-packages discover-licenses
     *     wp private-packages discover-licenses --format=table
     *     wp private-packages discover-licenses --plugin=advanced-custom-fields-pro
     *     wp private-packages discover-licenses --filter=woocommerce
     *     wp private-packages discover-licenses --recipe=edd
     *
     * @when after_wp_load
     */
    /**
     * @param  array<int, string>  $args
     * @param  array<string, string>  $assocArgs
     */
    public function __invoke(array $args, array $assocArgs): void
    {
        $format = $assocArgs['format'] ?? 'json';
        $pluginFilter = $assocArgs['plugin'] ?? null;
        $slugFilter = $assocArgs['filter'] ?? null;
        $recipeFilter = $assocArgs['recipe'] ?? null;

        if (! $this->discoverer->hasValidPackages()) {
            \WP_CLI::error('Could not load the packages list from private-packages.com. Please try again later.');
        }

        $installedPlugins = $pluginFilter
            ? [$pluginFilter]
            : $this->discoverer->getInstalledPluginSlugs();

        if ($slugFilter !== null) {
            $installedPlugins = array_values(
                array_filter($installedPlugins, fn ($slug) => str_contains($slug, $slugFilter))
            );
        }

        $results = $this->discoverer->discover($installedPlugins);

        if ($recipeFilter !== null) {
            $results = array_values(
                array_filter($results, fn ($result) => ($result['export']['recipe'] ?? null) === $recipeFilter)
            );
        }

        $tableRows = [];
        $jsonRows = [];

        foreach ($results as $result) {
            $status = $result['status'];

            if ($status === PackageStatus::Ready) {
                $jsonRows[] = $result['export'];
                $tableRows[] = [
                    'plugin' => $result['name'],
                    'import_settings' => \WP_CLI::colorize('%G'.json_encode($result['export']).'%n'),
                ];

                continue;
            }

            $color = match ($status) {
                PackageStatus::LicenseFoundNotExportable => '%y',
                default => '',
            };
            $tableRows[] = [
                'plugin' => $result['name'],
                'import_settings' => $color ? \WP_CLI::colorize($color.$status->label().'%n') : $status->label(),
            ];
        }

        if ($format === 'json') {
            \WP_CLI::line(json_encode($jsonRows) ?: '[]');

            return;
        }

        if (empty($tableRows)) {
            \WP_CLI::warning('No matching licensed plugins found.');

            return;
        }

        \WP_CLI\Utils\format_items('table', $tableRows, ['plugin', 'import_settings']);
    }
}
