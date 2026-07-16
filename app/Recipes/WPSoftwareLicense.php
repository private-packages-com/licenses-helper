<?php

namespace PrivatePackages\DiscoverLicensesCommand\Recipes;

class WPSoftwareLicense extends Recipe
{
    /** @return array<string, mixed>|null */
    public function getCredentials(): ?array
    {
        return $this->resolveSecrets();
    }
}
