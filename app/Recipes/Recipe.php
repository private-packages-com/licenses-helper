<?php

namespace PrivatePackages\DiscoverLicensesCommand\Recipes;

abstract class Recipe
{
    /** @param array<string, mixed> $package */
    public function __construct(protected readonly array $package) {}

    /** @return array<string, mixed>|null */
    abstract public function getCredentials(): ?array;

    /**
     * @param  array<string, mixed>  $settings
     * @return array<string, mixed>
     */
    public function getSettings(array $settings): array
    {
        return $settings;
    }

    /** @return array<string, mixed>|null */
    protected function resolveSecrets(): ?array
    {
        $secrets = $this->package['settings']['secrets'] ?? null;
        if (! $secrets) {
            return null;
        }

        $credentials = [];
        foreach ($secrets as $credentialKey => $source) {
            $value = maybe_unserialize(get_option($source['wp_options_key']));
            if (isset($source['wp_options_path'])) {
                foreach (explode('.', $source['wp_options_path']) as $segment) {
                    if (is_null($value)) {
                        break;
                    }
                    $value = is_object($value) ? ($value->$segment ?? null) : ($value[$segment] ?? null);
                }
            }
            if ($value && ($source['base64_decode'] ?? false)) {
                $value = base64_decode($value);
            }
            if ($value) {
                $credentials[$credentialKey] = $value;
            }
        }

        return $credentials ?: null;
    }
}
