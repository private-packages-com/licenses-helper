<?php

namespace PrivatePackages\DiscoverLicensesCommand\Enums;

enum PackageStatus
{
    case NoPreset;
    case NotExportable;
    case LicenseFoundNotExportable;
    case LicenseNotFound;
    case Ready;

    public function label(): string
    {
        return match ($this) {
            self::NoPreset => 'No preset available. You can try to manually add this plugin in Private Packages. Contact us if you need help.',
            self::NotExportable => 'Supported in Private Packages, but does not support exporting.',
            self::LicenseFoundNotExportable => 'License found, but this plugin does not support exporting.',
            self::LicenseNotFound => 'Supported in Private Packages, but no license was found.',
            self::Ready => 'License found.',
        };
    }
}
