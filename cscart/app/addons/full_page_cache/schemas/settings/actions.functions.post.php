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

/**
 * Hook is used to check whether an add-on can be enabled and execute enable/disable-related actions.
 *
 * @param $new_status
 * @param $old_status
 */
function fn_settings_actions_addons_full_page_cache(&$new_status, $old_status)
{
    if ($new_status == 'A') {
        /** @var \Tygh\Addons\FullPageCache\Addon $addon */
        $addon = Tygh::$app['addons.full_page_cache'];
        if (!$addon->canBeEnabled()) {
            $new_status = 'D';
            return;
        }
    }
}

/**
 * Hook is used to change status of the unmanaged addon together with main addon.
 *
 * @param $new_status
 */
function fn_settings_actions_addons_post_full_page_cache($new_status)
{
    /** @var \Tygh\Addons\FullPageCache\Addon $addon */
    $addon = Tygh::$app['addons.full_page_cache'];

    // Add-on have been enabled
    if ($new_status == 'A') {
        $addon->onAddonEnable();
    } // Add-on have been disabled
    if ($new_status == 'D') {
        $addon->onAddonDisable();
    }

    fn_update_addon_status('full_page_cache_unmanaged', $new_status, false, false, true);

    fn_full_page_cache_unrequire_vcl_regeneration();
}
