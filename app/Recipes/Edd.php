<?php

namespace PrivatePackages\DiscoverLicensesCommand\Recipes;

class Edd extends Recipe
{
    /** @return array<string, mixed>|null */
    public function getCredentials(): ?array
    {
        return $this->resolveSecrets();
    }
}
