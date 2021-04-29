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

use Tygh\Addons\FullPageCache\Addon;
use Tygh\Registry;

require_once __DIR__ . '/vendor/autoload.php';
Tygh::$app['class_loader']->add('', __DIR__);
Tygh::$app->register(new \Tygh\Providers\FullPageCacheProvider());

/**
 * Hook is used to install the unmanaged addon together with main addon.
 */
function fn_full_page_cache_install()
{
    fn_install_addon('full_page_cache_unmanaged', false, false, true);
}

/**
 * Hook is used to install the unmanaged addon together with main addon.
 */
function fn_full_page_cache_uninstall()
{
    fn_uninstall_addon('full_page_cache_unmanaged', false, true);
}

/**
 * Hook is used to add path of directory containing Varnish VCL files to backup.
 *
 * @param $backup_files
 */
function fn_full_page_cache_data_keeper_backup_files(&$backup_files)
{
    /** @var Addon $addon */
    $addon = Tygh::$app['addons.full_page_cache'];

    $backup_files[] = fn_get_rel_dir($addon->getVarnishVCLDirectory());
}

/**
 * Hook is used to determine whether ESI-rendering should be enabled for the block being currently rendered.
 *
 * @param $block
 * @param $block_schema
 * @param $params
 * @param $block_content
 */
function fn_full_page_cache_render_block_pre($block, $block_schema, &$params, &$block_content)
{
    if (AREA == 'A') {
        $params['esi_enabled'] = false;
    } else {
        $params['esi_enabled'] = isset($params['esi_enabled'])
            ? (bool) $params['esi_enabled']
            : (isset($block_schema['session_dependent']) ? (bool) $block_schema['session_dependent'] : false);
    }

    if ($params['esi_enabled']) {
        $params['use_cache'] = false;
    }
}

function fn_full_page_cache_init_templater_post(&$view){
    $view->unregisterFilter('output', array('Tygh\SmartyEngine\Filters', 'outputSecurityHash'));
}

function fn_full_page_cache_smarty_block_hook_post($params, $content, $overrides, $smarty, &$hook_content){      
    if($params['name'] == 'index:body'){
        $addon = Tygh::$app['addons.full_page_cache'];

        $hook_content = $addon->renderESIForCSRF($hook_content);
    }
}

/**
 * Hook is used to wrap block contents with ESI directive if this is needed.
 *
 * @param $block_schema
 * @param $block
 * @param $block_content
 * @param $params
 */
function fn_full_page_cache_render_block_post(
    $block,
    $block_schema,
    &$block_content,
    $load_block_from_cache,
    $display_this_block,
    $params
) {
    /** @var Addon $addon */
    $addon = Tygh::$app['addons.full_page_cache'];

    if ($params['esi_enabled'] && $display_this_block) {
        $block_content = $addon->renderESIForBlock(
            $block, $block_content, CART_LANGUAGE,
            Registry::get('config.current_location'),
            Registry::get('config.current_url'),
            (defined('DEVELOPMENT') && DEVELOPMENT)
        );
    }
}

function fn_full_page_cache_dispatch_before_send_response($status, $area, $controller, $mode, $action)
{
    if ($area == 'C') {
        /** @var Addon $addon */
        $addon = Tygh::$app['addons.full_page_cache'];

        foreach (Registry::getCachedKeys() as $cached_key) {
            if (isset($cached_key['condition']) && is_array($cached_key['condition'])) {
                $addon->registerCacheTags($cached_key['condition']);
            }
        }

        header($addon->getPageCacheTagsHeader());
    }
}

function fn_full_page_cache_disable_cache_by_cookie()
{
    fn_set_cookie('disable_cache', 'Y', COOKIE_ALIVE_TIME);
}

function fn_full_page_cache_enable_cache_by_cookie()
{
    setcookie('disable_cache', 'N', 1, Registry::ifGet('config.current_path', '/'));
}

/**
 * Hook is used to set separate cookie for users that are logged in.
 */
function fn_full_page_cache_sucess_user_login()
{
    if (AREA == 'C') {
        fn_full_page_cache_disable_cache_by_cookie();
    }
}

/**
 * Hook is used to remove cookie that marks logged in users after logout.
 */
function fn_full_page_cache_user_logout_after()
{
    if (AREA == 'C') {
        fn_full_page_cache_enable_cache_by_cookie();
    }
}

/**
 * Hook is used to invalidate page cache records.
 */
function fn_full_page_cache_registry_save_pre($changed_tables)
{
    /** @var Addon $addon */
    $addon = Tygh::$app['addons.full_page_cache'];

    $addon->invalidateByTags(array_map(
        array($addon, 'mapStringToCacheTag'),
        array_keys($changed_tables)
    ));
}

/**
 * Hook is used to add currency and language query string parameters to the store's each internal URL.
 */
function fn_full_page_cache_url_post(&$output_url, $area, $input_url, $protocol, $company_id_in_url, $lang_code)
{
    if ($area !== 'C') {
        return;
    }

    // Cart is not initialized yet
    if (!defined('CART_LANGUAGE')) {
        return;
    }

    $url = new \Tygh\Tools\Url($output_url);

    /** @var \Tygh\Addons\FullPageCache\Storefront $storefront */
    $storefront = Tygh::$app['addons.full_page_cache.storefront'];

    // Only internal (leading to the storefront pages) URLs should be modified
    if (!$storefront->containsUrl($url)) {
        return;
    }

    // URL contains the protocol (scheme), but it's not a HTTP(S)
    if ($url->getProtocol() !== null && !in_array($url->getProtocol(), array('http', 'https'))) {
        return;
    }

    // Now, all checks were passed;

    $query_params = $url->getQueryParams();

    $currency = array(
        'param_key' => 'currency',
        'value' => CART_SECONDARY_CURRENCY,
        'default' => CART_PRIMARY_CURRENCY,
        'remove' => false,
    );

    // @FIXME: We have to parse "sl" query string parameter from the $input_url,
    // because SEO addon removes it from the $output_url.
    $input_url_obj = new \Tygh\Tools\Url($input_url);
    $input_url_query_params = $input_url_obj->getQueryParams();

    $language = array(
        'param_key' => 'sl',
        'value' => isset($input_url_query_params['sl']) ? $input_url_query_params['sl'] : CART_LANGUAGE,
        'default' => Registry::get('settings.Appearance.frontend_default_language'),
        'remove' => Registry::get('addons.seo.status') == 'A' && Registry::get('addons.seo.seo_language') == 'Y'
    );

    foreach (array($currency, $language) as $url_passhtru_param) {

        if ($url_passhtru_param['remove']) {
            unset ($query_params[$url_passhtru_param['param_key']]);
        } else {
            if (!isset($query_params[$url_passhtru_param['param_key']])) {
                $query_params[$url_passhtru_param['param_key']] = $url_passhtru_param['value'];
            }
            if ($query_params[$url_passhtru_param['param_key']] == $url_passhtru_param['default']) {
                unset ($query_params[$url_passhtru_param['param_key']]);
            }
        }
    }

    $url->setQueryParams($query_params);

    $output_url = $url->build();
}

/**
 * Adds a notification that warns about requirement to regenerate VCL.
 *
 * @return void
 */
function fn_full_page_cache_notify_about_vcl_regeneration_requirement()
{
    fn_set_notification(
        'W',
        __('warning'),
        __('full_page_cache.warning_vcl_regeneration_required'),
        'S',
        'full_page_cache.warning_vcl_regeneration_required'
    );
}

/**
 * @return bool Whether VCL regeneration is required.
 */
function fn_full_page_cache_is_vcl_regeneration_required()
{
    return false;
    
    return fn_get_storage_data('full_page_cache__vcl_regeneration_required') == 'Y';
}

/**
 * Sets notification that warns user about enabling VCL regeneration requirement.
 * This function is being called after the data required for VCL contents is being changed.
 *
 * @return void
 */
function fn_full_page_cache_require_vcl_regeneration()
{
    // Execute SQL query only if the flag is not set at current runtime
    if (!fn_full_page_cache_is_vcl_regeneration_required()) {
        fn_set_storage_data('full_page_cache__vcl_regeneration_required', 'Y');
    }
}

/**
 * Removes notification that warns user about enabling VCL regeneration requirement.
 * This function is being called after VCL file was regenerated.
 *
 * @return void
 */
function fn_full_page_cache_unrequire_vcl_regeneration()
{
    fn_set_storage_data('full_page_cache__vcl_regeneration_required', 'N');
    fn_delete_notification('full_page_cache.warning_vcl_regeneration_required');
}

function fn_full_page_cache_update_company()
{
    fn_full_page_cache_require_vcl_regeneration();
}

function fn_full_page_cache_create_seo_name_post($object_seo_name, $object_id, $object_type, $object_name)
{
    // Only require regeneration when dispatch-based SEO rule was added/changed
    if ($object_type == 's') {
        fn_full_page_cache_require_vcl_regeneration();
    }
}

function fn_full_page_cache_settings_update_value_by_id_post()
{
    fn_full_page_cache_require_vcl_regeneration();
}

function fn_full_page_cache_update_addon_status_post()
{
    fn_full_page_cache_require_vcl_regeneration();
}

function fn_full_page_cache_update_customization_mode($modes, $enabled_modes) {
    if (empty($enabled_modes)) {
        fn_full_page_cache_enable_cache_by_cookie();
    } else {
        fn_full_page_cache_disable_cache_by_cookie();
    }
}
