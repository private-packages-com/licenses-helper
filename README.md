# Private Packages Licenses Helper

Discover license credentials for installed premium plugins and export them for import into [Private Packages](https://private-packages.com).

## Installation

**Option 1 — Download zip**

1. Download the plugin zip from GitHub.
2. Go to **Plugins > Add New > Upload Plugin** and upload the zip.
3. Activate the plugin.

**Option 2 — WP-CLI**

```bash
wp package install private-packages-com/licenses-helper
```

## CLI Usage

Run the command to discover licenses for all installed plugins:

```bash
wp private-packages discover-licenses
```

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

## WP Admin Usage

1. Go to **Tools > Licenses Helper** in the WordPress admin.
2. All supported plugins are listed with their license status.
3. Check the plugins you want to export (all are selected by default).
4. Click **Generate Export**.
5. A JSON block will appear at the top of the page.

## Importing into Private Packages

1. Copy the generated JSON.
2. Go to **Private Packages > Packages**.
3. Click **Import from JSON**.
4. Paste the JSON and submit.
