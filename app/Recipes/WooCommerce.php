<?php

namespace PrivatePackages\DiscoverLicensesCommand\Recipes;

class WooCommerce extends Recipe
{
    /** @return array<string, mixed>|null */
    public function getCredentials(): ?array
    {
        $raw = get_option('woocommerce_helper_data');
        if (! $raw) {
            return null;
        }

        $data = maybe_unserialize($raw);
        $auth = $data['auth'] ?? null;

        if (empty($auth['access_token']) || empty($auth['access_token_secret'])) {
            return null;
        }

        return [
            'access_token' => $auth['access_token'],
            'access_token_secret' => $auth['access_token_secret'],
        ];
    }
}
