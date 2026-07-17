<?php

namespace PrivatePackages\DiscoverLicensesCommand;

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
     * ## EXAMPLES
     *
     *     wp private-packages discover-licenses
     *     wp private-packages discover-licenses --format=table
     *     wp private-packages discover-licenses --plugin=advanced-custom-fields-pro
     *     wp private-packages discover-licenses --filter=woocommerce
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

        $installedPlugins = $pluginFilter
            ? [$pluginFilter]
            : $this->discoverer->getInstalledPluginSlugs();

        if ($slugFilter !== null) {
            $installedPlugins = array_values(
                array_filter($installedPlugins, fn ($slug) => str_contains($slug, $slugFilter))
            );
        }

        $results = $this->discoverer->discover($installedPlugins);
        $tableRows = [];
        $jsonRows = [];

        foreach ($results as $result) {
            if (! $result['supported']) {
                $tableRows[] = [
                    'plugin' => $result['slug'],
                    'import_settings' => \WP_CLI::colorize('%rNo preset available. You can try to manually add this plugin in Private Packages. Contact us if you need help.%n'),
                ];

                continue;
            }

            if (! $result['has_credentials']) {
                $tableRows[] = [
                    'plugin' => $result['name'],
                    'import_settings' => \WP_CLI::colorize('%ySupported in Private Packages, but can\'t be exported%n'),
                ];

                continue;
            }

            $jsonRows[] = $result['export'];
            $tableRows[] = [
                'plugin' => $result['name'],
                'import_settings' => \WP_CLI::colorize('%G'.json_encode($result['export']).'%n'),
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
