<?php

namespace PrivatePackages\DiscoverLicensesCommand;

use PrivatePackages\DiscoverLicensesCommand\Enums\PackageStatus;

class LicenseDiscoverer
{
    private const TRANSIENT_KEY = 'private_packages_packages_json';

    /** @var array<string, mixed>|null */
    private ?array $packages = null;

    /** @var array<string, string> */
    private array $aliases = [];

    private function fetchPackagesJson(): string
    {
        $cached = get_transient(self::TRANSIENT_KEY);

        if ($cached !== false) {
            return $cached;
        }

        $response = wp_remote_get('https://private-packages.com/files/packages.json');
        $contents = wp_remote_retrieve_body($response);
        $decoded = json_decode($contents, true);

        if (is_array($decoded) && count($decoded) > 0) {
            set_transient(self::TRANSIENT_KEY, $contents, 12 * HOUR_IN_SECONDS);
        }

        return $contents;
    }

    public function hasValidPackages(): bool
    {
        return count($this->getPackages()) > 0;
    }

    /** @return array<string, mixed> */
    private function getPackages(): array
    {
        if ($this->packages !== null) {
            return $this->packages;
        }

        $this->packages = json_decode($this->fetchPackagesJson(), true) ?? [];

        foreach ($this->packages as $key => $package) {
            foreach ($package['aliases'] ?? [] as $alias) {
                $this->aliases[$alias] = $key;
            }
        }

        return $this->packages;
    }

    /** @return array<int, string> */
    public function getInstalledPluginSlugs(): array
    {
        if (! function_exists('get_plugins')) {
            require_once ABSPATH.'wp-admin/includes/plugin.php';
        }

        $slugs = [];
        foreach (array_keys(get_plugins()) as $pluginFile) {
            $slugs[] = explode('/', $pluginFile)[0];
        }

        return $slugs;
    }

    /**
     * @param  array<int, string>  $slugs
     * @return array<int, array<string, mixed>>
     */
    public function discover(array $slugs): array
    {
        $results = [];

        $packages = $this->getPackages();

        foreach ($slugs as $slug) {
            $resolvedSlug = $this->aliases[$slug] ?? $slug;

            if (! isset($packages[$resolvedSlug])) {
                $results[] = [
                    'slug' => $slug,
                    'name' => $slug,
                    'status' => PackageStatus::NoPreset,
                    'export' => null,
                ];

                continue;
            }

            $package = $packages[$resolvedSlug];
            $exportable = ($package['exportable'] ?? false) === true;
            $recipe = $this->resolveRecipe($package);
            $secrets = $recipe?->getCredentials();

            if ($secrets === null) {
                $results[] = [
                    'slug' => $slug,
                    'name' => $package['name'],
                    'status' => $exportable ? PackageStatus::LicenseNotFound : PackageStatus::NotExportable,
                    'export' => null,
                ];

                continue;
            }

            $settings = $package['settings'] ?? [];
            unset($settings['secrets']);
            $settings['source_url'] = home_url();
            $settings = $recipe->getSettings($settings);

            $results[] = [
                'slug' => $slug,
                'name' => $package['name'],
                'status' => $exportable ? PackageStatus::Ready : PackageStatus::LicenseFoundNotExportable,
                'export' => [
                    'recipe' => $package['recipe'],
                    'type' => $package['type'],
                    'slug' => $package['slug'],
                    'settings' => $settings,
                    'secrets' => $secrets,
                ],
            ];
        }

        return $results;
    }

    /** @param array<string, mixed> $package */
    private function resolveRecipe(array $package): ?Recipes\Recipe
    {
        $class = __NAMESPACE__.'\\Recipes\\'.str_replace('_', '', ucwords($package['recipe'], '_'));

        if (! class_exists($class)) {
            return null;
        }

        $instance = new $class($package);
        if (! $instance instanceof Recipes\Recipe) {
            return null;
        }

        return $instance;
    }
}
