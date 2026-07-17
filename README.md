# Private Packages Licenses Helper

[![Latest Release](https://img.shields.io/github/v/release/private-packages-com/licenses-helper)](https://github.com/private-packages-com/licenses-helper/releases/latest)

Discover license credentials for installed premium plugins and export them for import into [Private Packages](https://private-packages.com).

## Requirements

- PHP 8.1 or higher
- WordPress with WP-CLI

## Installation & Usage

### Option 1 — WP-CLI package

Install as a WP-CLI package (no WordPress plugin activation required):

```bash
wp package install private-packages-com/licenses-helper
```

Then use the CLI command to discover licenses:

```bash
wp private-packages discover-licenses
```

### Option 2 — WordPress plugin

**Install via zip:**

1. Download the plugin zip from the [latest release](https://github.com/private-packages-com/licenses-helper/releases/latest/download/private-packages-licenses-helper.zip).
2. Go to **Plugins > Add New > Upload Plugin** and upload the zip.
3. Activate the plugin.

Once activated, you can discover licenses in two ways:

**WP Admin:** Go to **Tools > Licenses Helper**, check the plugins you want to export (all are selected by default), and click **Generate Export**. A JSON block will appear at the top of the page.

**WP-CLI:** Use the same CLI command as above.

---

## CLI Reference

**Options**

| Option              | Description                                         |
| ------------------- | --------------------------------------------------- |
| `--format=<format>` | Output format: `json` (default) or `table`          |
| `--plugin=<slug>`   | Only return data for a specific plugin slug         |
| `--filter=<string>` | Only return plugins whose slug contains this string |

**Examples**

```bash
# Output JSON for all plugins (default)
wp private-packages discover-licenses

# Output as a table
wp private-packages discover-licenses --format=table

# Single plugin
wp private-packages discover-licenses --plugin=advanced-custom-fields-pro

# Filter by partial slug
wp private-packages discover-licenses --filter=woocommerce
```

The JSON output can be piped or copied directly for import into Private Packages.

## Importing into Private Packages

1. Copy the generated JSON.
2. Go to **Private Packages > Packages**.
3. Click **Import from JSON**.
4. Paste the JSON and submit.

## Understanding the Results

| Status                                                        | Meaning                                                                                                                                                                                                                                                                                                                        |
| ------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| **No preset available. You can try to manually add this plugin in Private Packages. Contact us if you need help.** | The plugin is not in the Private Packages list. This either means it is not a premium plugin (in which case it should be installed via [wp-packages.org](https://wp-packages.org)) or it is a premium plugin that Private Packages does not yet have a preset for. You can add it manually, or [contact us](https://private-packages.com) if you need help. |
| **Supported in Private Packages, but can't be exported**      | The plugin is in the Private Packages list, but no credentials were found. This could mean the license credentials are not stored in the database, or this helper does not yet know where to look for them. Add this plugin to Private Packages manually.                                                                      |
