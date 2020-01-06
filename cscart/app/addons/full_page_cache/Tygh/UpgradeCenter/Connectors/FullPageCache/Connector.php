<?php
namespace Tygh\UpgradeCenter\Connectors\FullPageCache;

use Tygh\Addons\SchemesManager;
use Tygh\Registry;
use Tygh\UpgradeCenter\Connectors\BaseAddonConnector;

/**
 * Marketplace add-on upgrade connector
 */
class Connector extends BaseAddonConnector
{
    /**
     * Put your add-on identifier here
     */
    const ADDON_IDENTIFIER = 'full_page_cache';

    /**
     * @var string Version of product addon runs in
     */
    protected $environment_version;

    /**
     * Prepares request data for request to Upgrade server (Check for the new upgrades)
     *
     * @return array Prepared request information
     */
    public function getConnectionData()
    {
        $request_data = parent::getConnectionData();
        $request_data['url'] = $this->updates_server;
        $request_data['data']['product_id'] = $this->addon_id;
        $request_data['data']['dispatch'] = 'product_packages.get_upgrades';
        // "ver" is used for addon version
        // while "product_version" is for environment version
        $request_data['data']['product_version'] = $this->environment_version;

        return $request_data;
    }

    /**
     * Downloads upgrade package from the Upgade server
     *
     * @param  array  $schema       Package schema
     * @param  string $package_path Path where the upgrade pack must be saved
     *
     * @return bool   True if upgrade package was successfully downloaded, false otherwise
     */
    public function downloadPackage($schema, $package_path)
    {
        $package_url = fn_url(
            $this->updates_server . (strpos($this->updates_server, '?') !== false ? '&' : '?') .
            http_build_query(array(
                'dispatch' => 'product_packages.get_package',
                'package_id' => $schema['package_id'],
                'product_id' => $this->addon_id,
                'license_number' => $this->license_number
            ))
        );
        $data = fn_get_contents($package_url);
        if (!empty($data)) {
            fn_put_contents($package_path, $data);
            $result = array(true, '');
        } else {
            $result = array(false, __('text_uc_cant_download_package'));
        }

        return $result;
    }

    /**
     * Gets Marketplace product identifier from addon.xml scheme
     */
    public static function getMarketplaceProductId()
    {
        $scheme_path = Registry::get('config.dir.addons') . self::ADDON_IDENTIFIER . '/addon.xml';
        if (file_exists($scheme_path)) {
            $scheme = @simplexml_load_file($scheme_path);
            // <marketplace_product_id> is automatically added into addon.xml by Marketplace
            if (isset($scheme->marketplace_product_id)) {
                return (int) $scheme->marketplace_product_id;
            }
        }

        return 0;
    }

    public function __construct()
    {
        parent::__construct();
        // Initial settings
        $this->addon_id = self::getMarketplaceProductId();
        $addon_scheme = SchemesManager::getScheme(self::ADDON_IDENTIFIER);
        $this->updates_server = Registry::get('config.resources.marketplace_url');
        $this->product_name = $addon_scheme->getName();
        $this->product_version = $addon_scheme->getVersion();
        $this->environment_version = PRODUCT_VERSION;
        $this->product_edition = PRODUCT_EDITION;
        $this->license_number = Registry::get('addons.' . self::ADDON_IDENTIFIER . '.marketplace_license_number');
    }
}