<?php

namespace PrivatePackages\DiscoverLicensesCommand\Recipes;

class GravityForms extends Recipe
{
    /** @return array<string, mixed>|null */
    public function getCredentials(): ?array
    {
        $licenseKey = get_option('rg_gforms_key');
        if (! $licenseKey) {
            return null;
        }

        return [
            'license_key' => $licenseKey,
        ];
    }
}
