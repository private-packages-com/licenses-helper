<?php

namespace PrivatePackages\DiscoverLicensesCommand\Recipes;

class Acf extends Recipe
{
    /** @return array<string, mixed>|null */
    public function getCredentials(): ?array
    {
        $raw = get_option('acf_pro_license');
        $option = maybe_unserialize(base64_decode($raw) ?: $raw);
        $licenseKey = is_array($option)
            ? ($option['key'] ?? null)
            : $option;

        if (! $licenseKey) {
            return null;
        }

        return [
            'license_key' => $licenseKey,
        ];
    }
}
