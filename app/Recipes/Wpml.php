<?php

namespace PrivatePackages\DiscoverLicensesCommand\Recipes;

class Wpml extends Recipe
{
    /** @return array<string, mixed>|null */
    public function getCredentials(): ?array
    {
        $raw = get_option('wp_installer_settings');
        if (! $raw) {
            return null;
        }

        $decompressed = gzuncompress(base64_decode($raw));
        $data = $decompressed !== false ? @unserialize($decompressed) : false;
        if (! $data) {
            return null;
        }

        $wpml = $data['repositories']['wpml'] ?? null;
        if (! $wpml) {
            return null;
        }

        $siteKey = $wpml['subscription']['key'] ?? null;

        if (! $siteKey) {
            return null;
        }

        return [
            'site_key' => $siteKey,
        ];
    }

    /**
     * @param  array<string, mixed>  $settings
     * @return array<string, mixed>
     */
    public function getSettings(array $settings): array
    {
        $settings['source_url'] = site_url();

        return $settings;
    }
}
