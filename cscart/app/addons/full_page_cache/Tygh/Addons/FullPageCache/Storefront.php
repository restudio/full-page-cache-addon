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

namespace Tygh\Addons\FullPageCache;

use Tygh\Tools\Url;

class Storefront
{
    public $id;

    public $name;

    public $http_host;

    public $http_path;

    public $https_host;

    public $https_path;

    public $session_cookie_name;

    /**
     * Checks whether given URL belongs to the storefront.
     *
     * @param \Tygh\Tools\Url $url URL to match
     *
     * @return bool Whether the given URL leads to the page that is belong to the storefront
     */
    public function containsUrl(Url $url)
    {
        $storefront_url = new Url();

        switch ($url->getProtocol()) {
            case 'http':
                $storefront_url->setHost($this->http_host);
                $storefront_url->setPath($this->http_path);
                break;
            case 'https':
                $storefront_url->setHost($this->https_host);
                $storefront_url->setPath($this->https_path);
                break;
            default:
                return false;
        }

        return $url->containsAsSubpath($storefront_url);
    }
}
