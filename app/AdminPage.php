<?php

namespace PrivatePackages\DiscoverLicensesCommand;

class AdminPage
{
    public function __construct(private readonly LicenseDiscoverer $discoverer) {}

    public function addMenuPage(): void
    {
        add_management_page(
            'Licenses Helper',
            'Licenses Helper',
            'manage_options',
            'private-packages-licenses-helper',
            [$this, 'renderPage']
        );
    }

    public function renderPage(): void
    {
        $installedSlugs = $this->discoverer->getInstalledPluginSlugs();
        $allResults = $this->discoverer->discover($installedSlugs);

        $export = null;
        if (isset($_POST['discover_licenses_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['discover_licenses_nonce'])), 'discover_licenses')) {
            $selected = array_map('sanitize_text_field', (array) ($_POST['plugins'] ?? []));
            $selectedResults = array_filter($allResults, fn ($r) => in_array($r['slug'], $selected, true));
            $exportRows = array_values(array_filter(array_column(array_values($selectedResults), 'export')));
            $export = json_encode($exportRows, JSON_PRETTY_PRINT);
        }

        ?>
        <div class="wrap">
            <h1>Discover Licenses</h1>
            <p>Select the plugins you want to include in the export, then click <strong>Generate Export</strong>.</p>

            <?php if ($export !== null) { ?>
                <h2>Export</h2>
                <p>Copy the JSON below, go to <strong>Private Packages &gt; Packages</strong>, click <strong>Import from JSON</strong>, paste the JSON and submit.</p>
                <textarea
                    readonly
                    rows="20"
                    style="width:100%;font-family:monospace;font-size:13px;"
                    onclick="this.select()"
                ><?php echo esc_textarea($export); ?></textarea>
                <br><br>
            <?php } ?>

            <form method="post">
                <?php wp_nonce_field('discover_licenses', 'discover_licenses_nonce'); ?>

                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <td class="check-column">
                                <input type="checkbox" id="cb-select-all" checked>
                            </td>
                            <th>Plugin</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allResults as $result) { ?>
                            <tr>
                                <th class="check-column">
                                    <input
                                        type="checkbox"
                                        name="plugins[]"
                                        value="<?php echo esc_attr($result['slug']); ?>"
                                        checked
                                    >
                                </th>
                                <td><?php echo esc_html($result['name']); ?></td>
                                <td>
                                    <?php if ($result['has_credentials']) { ?>
                                        <span style="color:#00a32a">&#10003; License found</span>
                                    <?php } elseif ($result['supported']) { ?>
                                        <span style="color:#dba617">&#9888; Supported in Private Packages, but can&#8217;t be exported</span>
                                    <?php } else { ?>
                                        <span style="color:#999">&#8212; Not supported. Contact us if you want this package added.</span>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>

                <p>
                    <?php submit_button('Generate Export', 'primary', 'submit', false); ?>
                </p>
            </form>

        </div>

        <script>
        document.getElementById('cb-select-all').addEventListener('change', function () {
            document.querySelectorAll('input[name="plugins[]"]').forEach(function (cb) {
                cb.checked = this.checked;
            }.bind(this));
        });
        </script>
        <?php
    }
}
