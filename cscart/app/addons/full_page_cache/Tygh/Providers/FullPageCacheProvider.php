<?php
/***************************************************************************
 *                                                                          *
 *   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
 *                                                                          *
 * This  is  commercial  software,  only  users  who have purchased a valid *
 * license  and  accept  to the terms of the  License Agreement can install *
 * and use this program.                                                    *
 *                                                                          *
 ****************************************************************************
 * PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
 * "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
 ****************************************************************************/

namespace Tygh\Providers;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Tygh\Addons\FullPageCache\Addon;
use Tygh\Addons\FullPageCache\Storefront;
use Tygh\Addons\FullPageCache\Varnish\VclGenerator;
use Tygh\Application;
use Tygh\Registry;
use Tygh\Settings;
use ride\library\varnish\VarnishAdmin;
use Tygh\Tygh;
use Tygh\Web\Session;

/**
 * Class FullPageCacheProvider registers components used by "Full-page cache" add-on at Application container.
 *
 * @package Tygh\Providers
 */
final class FullPageCacheProvider implements ServiceProviderInterface
{
    /**
     * @inheritdoc
     */
    public function register(Container $pimple)
    {
        $provider = $this;

        Tygh::$app['addons.full_page_cache.settings'] = function (Application $app) {
            return Settings::instance()->getValues('full_page_cache', Settings::ADDON_SECTION, false);
        };

        Tygh::$app['addons.full_page_cache'] = function (Application $app) {
            $addon = new Addon($app, $app['addons.full_page_cache.settings']);

            $addon->setVarnishVCLDirectory(Registry::get('config.dir.var') . 'conf/varnish/');

            return $addon;
        };

        Tygh::$app['addons.full_page_cache.storefront'] = function (Application $app) {
            $storefront = new Storefront();

            /** @var Session $session */
            $session = $app['session'];

            $company_id = (int) (fn_get_runtime_company_id() ?: fn_get_default_company_id());

            $company_data = fn_get_company_data($company_id);

            $storefront_urls = fn_get_storefront_urls($company_id, $company_data);

            $storefront->id = $company_id;
            $storefront->name = $company_data['company'];
            $storefront->http_host = $storefront_urls['http_host'];
            $storefront->http_path = $storefront_urls['http_path'];
            $storefront->https_host = $storefront_urls['https_host'];
            $storefront->https_path = $storefront_urls['https_path'];

            $storefront->session_cookie_name = 'fpc_' . $session->getSessionNamePrefix() . 'customer' . $session->getSessionNameSuffix();

            return $storefront;
        };

        Tygh::$app['addons.full_page_cache.vcl_generator'] = function (Application $app) use ($provider) {
            $generator = new VclGenerator();
            $schema = fn_get_schema('full_page_cache', 'varnish', 'php', true);

            $generator->logged_in_cookie_name_pcre = 'disable_cache=Y';

            $generator->setCacheTTL($schema['cache_ttl']);
            $generator->addNonCachedPaths($schema['disable_for_paths']);
            $generator->addNonCachedDispatches($schema['disable_for_dispatches']);
            $generator->addNonCachedExtensions($schema['disable_for_extensions']);
            $generator->setStorefront($app['addons.full_page_cache.storefront']);

            return $generator;
        };

        Tygh::$app['addons.full_page_cache.varnishadm'] = function (Application $app) {
            return new VarnishAdmin(
                $app['addons.full_page_cache.settings']['varnish_host'],
                $app['addons.full_page_cache.settings']['varnish_adm_port'],
                $app['addons.full_page_cache.settings']['varnish_adm_secret']
            );
        };
    }
}