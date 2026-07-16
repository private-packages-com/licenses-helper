<?php

namespace PrivatePackages\DiscoverLicensesCommand\Recipes;

class WpRocket extends Recipe
{
    /** @return array<string, mixed>|null */
    public function getCredentials(): ?array
    {
        $settings = maybe_unserialize(get_option('wp_rocket_settings'));
        if (! $settings) {
            return null;
        }

        $consumerEmail = $settings['consumer_email'] ?? null;
        $consumerKey = $settings['consumer_key'] ?? null;

        if (! $consumerEmail || ! $consumerKey) {
            return null;
        }

        return [
            'consumer_email' => $consumerEmail,
            'consumer_key' => $consumerKey,
        ];
    }
}
