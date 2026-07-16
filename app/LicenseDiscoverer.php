<?php

namespace PrivatePackages\DiscoverLicensesCommand;

class LicenseDiscoverer
{
    /** @var array<string, mixed> */
    private array $packages;

    /** @var array<string, string> */
    private array $aliases = [];

    public function __construct()
    {
        $packagesFile = dirname(__DIR__).'/storage/packages.json';
        $contents = file_get_contents($packagesFile);
        $this->packages = json_decode($contents !== false ? $contents : '[]', true);

        foreach ($this->packages as $key => $package) {
            foreach ($package['aliases'] ?? [] as $alias) {
                $this->aliases[$alias] = $key;
            }
        }
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
     * @param array<int, string> $slugs
     * @return array<int, array<string, mixed>>
     */
    public function discover(array $slugs): array
    {
        $results = [];

        foreach ($slugs as $slug) {
            $resolvedSlug = $this->aliases[$slug] ?? $slug;

            if (! isset($this->packages[$resolvedSlug])) {
                $results[] = [
                    'slug' => $slug,
                    'name' => $slug,
                    'supported' => false,
                    'has_credentials' => false,
                    'export' => null,
                ];
                continue;
            }

            $package = $this->packages[$resolvedSlug];
            $recipe = $this->resolveRecipe($package);
            $secrets = $recipe?->getCredentials();

            if ($secrets === null) {
                $results[] = [
                    'slug' => $slug,
                    'name' => $package['name'],
                    'supported' => true,
                    'has_credentials' => false,
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
                'supported' => true,
                'has_credentials' => true,
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
