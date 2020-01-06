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

use Tygh\Registry;

$config = Registry::get('config');

$schema = array(
    // TTL for objects at cache, seconds
    'cache_ttl' => 90,

    // Which paths must not be cached at all
    'disable_for_paths' => array(
        'api.php',
        $config['admin_index'],
    ),

    // Which dispatches must not be cached at all
    'disable_for_dispatches' => array(
        'checkout.*',
        'orders.*',
        'wishlist.*',
        'product_features.add_product',
        'product_features.clear_list',
        'product_features.delete_product',
        'product_features.delete_feature',
        'product_features.compare',
    ),

    // Which file extensions must not be cached at all
    'disable_for_extensions' => array(
        'jpg', 'jpeg', 'png', 'gif', 'ico', 'tiff', 'tif', 'bmp', 'ppm', 'pgm', 'xcf', 'psd', 'webp', 'svg',
        'css', 'js',
        'html', 'txt',
        'woff', 'eot', 'otf', 'ttf',
        'zip', 'sql', 'tar', 'gz', 'tgz', 'bzip2', 'mp3', 'mp4', 'flv', 'ogg', 'swf',
    ),
);

if (fn_allowed_for('MULTIVENDOR')) {
    $schema['disable_for_files'][] = $config['vendor_index'];
}

return $schema;
