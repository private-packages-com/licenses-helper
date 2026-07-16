<?php

namespace PrivatePackages\DiscoverLicensesCommand\Recipes;

class FacetWP extends Recipe
{
    /** @return array<string, mixed>|null */
    public function getCredentials(): ?array
    {
        $licenseKey = get_option('facetwp_license');
        if (! $licenseKey) {
            return null;
        }

        return [
            'license_key' => $licenseKey,
        ];
    }
}
