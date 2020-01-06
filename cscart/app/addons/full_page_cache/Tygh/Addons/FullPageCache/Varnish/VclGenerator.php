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

namespace Tygh\Addons\FullPageCache\Varnish;

use Tygh\Addons\FullPageCache\Storefront;

/**
 * Class VclGenerator is used to generate Varnish's VCL files.
 * The generated file will contain common caching rules that use configuration values that were set up at the class
 * instance.
 *
 * @package Tygh\Addons\FullPageCache\Varnish
 */
final class VclGenerator
{
    public $logged_in_cookie_name_pcre;

    /**
     * @var Storefront
     */
    protected $storefront;

    /**
     * @var int Varnish cache objects TTL in seconds.
     */
    protected $cache_ttl = 90;

    /**
     * @var array List of the PCRE regular expressions (without delimiters) that will be used at VCL for matching
     *      non-cached URLs against HTTP request path.
     */
    protected $skip_url_regexp_list = array();

    protected $non_cached_extensions = array();

    protected $non_cached_dispatches = array();

    protected $non_cached_paths = array();

    protected $allowed_request_host_and_path_pairs = array();

    /**
     * @return Storefront
     */
    public function getStorefront()
    {
        return $this->storefront;
    }

    /**
     * @param Storefront $storefront
     */
    public function setStorefront(Storefront $storefront)
    {
        $this->storefront = $storefront;
    }

    /**
     * @return int Varnish cache objects TTL in seconds.
     */
    public function getCacheTTL()
    {
        return $this->cache_ttl;
    }

    /**
     * @param int $cache_ttl Sets Varnish cache objects TTL in seconds.
     */
    public function setCacheTTL($cache_ttl)
    {
        $this->cache_ttl = (int) $cache_ttl;
    }

    /**
     * @param string $regexp PCRE RegExp without delimiters that should be used at VCL for matching non-cached URLs
     *                       against HTTP request path.
     */
    public function skipUrlMatchingRegexp($regexp)
    {
        $this->skip_url_regexp_list[] = $regexp;
    }

    /**
     * @return array List of the PCRE regular expressions (without delimiters) that will be used at VCL for matching
     *               non-cached URLs against HTTP request path.
     */
    public function getSkippedUrlRegexpList()
    {
        return $this->skip_url_regexp_list;
    }

    /**
     * @return array
     */
    public function getNonCachedExtensions()
    {
        return $this->non_cached_extensions;
    }

    /**
     * @param array $non_cached_extensions
     */
    public function addNonCachedExtensions(array $non_cached_extensions)
    {
        $this->non_cached_extensions = array_merge($this->non_cached_extensions, $non_cached_extensions);
    }

    /**
     * @return array
     */
    public function getNonCachedDispatches()
    {
        return $this->non_cached_dispatches;
    }

    /**
     * @param array $non_cached_dispatches
     */
    public function addNonCachedDispatches(array $non_cached_dispatches)
    {
        $this->non_cached_dispatches = array_merge($this->non_cached_dispatches, $non_cached_dispatches);
    }

    /**
     * @param array $non_cached_dispatches
     */
    public function setNonCachedDispatches(array $non_cached_dispatches)
    {
        $this->non_cached_dispatches = $non_cached_dispatches;
    }

    /**
     * @return array
     */
    public function getNonCachedPaths()
    {
        return $this->non_cached_paths;
    }

    /**
     * @param array $non_cached_paths
     */
    public function addNonCachedPaths(array $non_cached_paths)
    {
        $this->non_cached_paths = array_merge($this->non_cached_paths, $non_cached_paths);
    }

    /**
     * @param array $non_cached_paths
     */
    public function setNonCachedPaths(array $non_cached_paths)
    {
        $this->non_cached_paths = $non_cached_paths;
    }

    public function prepareSkippedUrlRegexps(Storefront $storefront)
    {
        if (!empty($this->non_cached_paths)) {
            $storefront_path_regexp = empty($storefront->http_path)
                ? '\/'
                : preg_quote($this->normalizeHttpRequestPath($storefront->http_path) . '/', '/');

            $this->skipUrlMatchingRegexp(
                '^' . $storefront_path_regexp
                . '('
                . implode('|', array_map('preg_quote', array_unique($this->non_cached_paths)))
                . ')'
            );
        }

        if (!empty($this->non_cached_extensions)) {
            $this->skipUrlMatchingRegexp(
                '\.(' . implode('|', array_map('preg_quote', array_unique($this->non_cached_extensions))) . ')'
            );
        }

        if (!empty($this->non_cached_dispatches)) {
            $this->skipUrlMatchingRegexp(
                '(\?|&)dispatch=('
                . implode('|', array_map(function($dispatch) {
                    if (strpos($dispatch, '.*') !== false) {
                        list($dispatch) = explode('.', $dispatch);

                        $dispatch = preg_quote($dispatch);

                        $dispatch .= '\..+';
                    } else {
                        $dispatch = preg_quote($dispatch);
                    }

                    return $dispatch;
                }, array_unique($this->non_cached_dispatches)))
                . ')'
            );
        }
    }

    public function generate()
    {
        /**
         * Hook is executed before the start of generating Varnish cache enabling VCL file.
         *
         * @param \Tygh\Addons\FullPageCache\Varnish\VclGenerator $this VCL generator instance
         */
        fn_set_hook('varnish_generate_vcl_pre', $this);

        $this->prepareSkippedUrlRegexps($this->storefront);

        $generated_at = date(DATE_ISO8601);
        $generated_by = sprintf('%s v%s', PRODUCT_NAME, PRODUCT_VERSION . ' ' . PRODUCT_STATUS);

        $template = <<<TPL
# DO NOT MODIFY THIS FILE DIRECTLY.
# IT WAS GENERATED AUTOMATICALLY. ALL OF YOUR CHANGES WILL BE LOST!


# CS-Cart configuration file for Varnish
# Simtech, Ltd.
#
# {$generated_at}
# {$generated_by}

vcl 4.0;

backend default {
    .host = "127.0.0.1";
    .port = "8082";
}


###############################################################
## RECV                                                      ##
##                                                           ##
## Happens before we check if we have this in cache already. ##
###############################################################
sub vcl_recv {

    # We do not support SPDY or HTTP/2.0
    if (req.method == "PRI") {
        return (synth(405));
    }

    # Non-RFC2616 or CONNECT which is weird.
    if (req.method != "GET" &&
        req.method != "HEAD" &&
        req.method != "PUT" &&
        req.method != "POST" &&
        req.method != "TRACE" &&
        req.method != "OPTIONS" &&
        req.method != "DELETE"
    ) {
        return (pipe);
    }

    # We only deal with GET and HEAD by default
    if (req.method != "GET" && req.method != "HEAD") {
        return (pass);
    }

    # Not cacheable by default
    if (req.http.Authorization) {
        return (pass);
    }

    # Strip hash, server doesn't need it.
    if (req.url ~ "\#") {
        set req.url = regsub(req.url, "\#.*$", "");
    }

    # Strip a trailing ? if it exists
    if (req.url ~ "\?$") {
        set req.url = regsub(req.url, "\?$", "");
    }

    # We're unable to parse this header in order to pass correct GET-parameter to PHP backend.
    # The temporary solution is to disable language autodetection feature.
    if (req.http.Accept-Language) {
        unset req.http.Accept-Language;
    }

TPL;

        if (!empty($this->skip_url_regexp_list)) {
            $skip_url_pcre_cond = array();
            foreach ($this->skip_url_regexp_list as $pcre) {
                $skip_url_pcre_cond[] = 'req.url ~ "' . $pcre . '"';
            }

            $skip_url_pcre_cond = implode(" || ", $skip_url_pcre_cond);
            $template .= <<<TPL

    if ({$skip_url_pcre_cond}) {
        return (pass);
    }

TPL;
        }

        if ($this->storefront instanceof Storefront) {
            $storefront_condition = $this->generateStorefrontCondition($this->storefront);

            $template .= <<<VCL

    # Check whether request was made to the only allowed storefront
    {$storefront_condition} else {
        return (pass);
    }
VCL;
        }

        $template .= <<<TPL

    # Got ESI-request
    if (req.esi_level > 0 || req.url ~ "^\/esi.php") {
        set req.http.X-Varnish-ESI = true;

        return (pass);
    } else {
        # Client has cookies
        if (req.http.Cookie) {
            set req.http.X-Has-Cookies = true;
            if (req.http.Cookie ~ "{$this->logged_in_cookie_name_pcre}") {
                set req.http.X-Varnish-Disable-Cache = true;

                return (pass);
            } else {
                unset req.http.Cookie;
            }
        }
    }

    return (hash);
}

############################################################
## HASH
##
## Defines what is unique about a request.
## Executed when vcl_recv returns the hash action keyword.
############################################################
sub vcl_hash {
    hash_data(req.url);

    if (req.http.host) {
        hash_data(req.http.host);
    } else {
        hash_data(server.ip);
    }

    if (req.http.X-Has-Session) {
        hash_data("has_session");
    }

    return (lookup);
}

##########################################################################
## BACKEND RESPONSE
##
## Happens after we have read the response headers from the backend.
## Here you clean the response headers, removing silly Set-Cookie headers
## and other mistakes your backend does.
##########################################################################
sub vcl_backend_response {

    # beresp = BackEnd Response

    unset beresp.http.X-Powered-By;

    if (beresp.http.X-ESI-Response) {
        # ESI-response shouldn't set cookies
        unset beresp.http.Set-Cookie;

        # Disable caching ESI-requests
        set beresp.ttl = 0s;
        set beresp.uncacheable = true;

        # Do not parse nested ESI
        set beresp.do_esi = false;
    } else {
        if (bereq.http.X-Has-Cookies) {
            if (bereq.http.X-Varnish-Disable-Cache) {
                set beresp.ttl = 0s;
                set beresp.uncacheable = true;
                set beresp.do_esi = false;
            } else {
                if (bereq.http.X-Has-Session) {
                    set beresp.do_esi = true;
                    set beresp.uncacheable = false;
                }
            }
        } else {
            set beresp.do_esi = false;
        }
    }

    # Strip Set-Cookie headers and set object TTL
    # before saving page to cache
    if (beresp.ttl > 0s && !beresp.uncacheable) {
        unset beresp.http.Set-Cookie;
        set beresp.ttl = {$this->getCacheTTL()}s;
    }

    return (deliver);
}

#################################################################################################
## DELIVER
##
## Happens when we have all the pieces we need, and are about to send the response to the client.
#################################################################################################
sub vcl_deliver {
    set resp.http.X-Varnish-Hits = obj.hits;
    set resp.http.X-Req-URL = req.url;
    set resp.http.X-Req-Host = req.http.host;
    set resp.http.X-Varnish-Disable-Cache = req.http.X-Varnish-Disable-Cache;
    set resp.http.X-Has-Session = req.http.X-Has-Session;
    set resp.http.X-Req-Cookie = req.http.Cookie;
}
TPL;

        return $template;
    }

    /**
     * Generates PCRE that should be used to match whether one HTTP request path belongs to the given path.
     *
     * @param string|array $path HTTP request path(s) to match with.
     *
     * @return string Regular expression without delimiters
     */
    public function getHttpRequestPathMatchRegexp($path)
    {
        if (!is_array($path)) {
            $path = (array) $path;
        }

        foreach ($path as &$item) {
            $item = $this->normalizeHttpRequestPath($item);
            $item = preg_quote($item, '/');
        }

        $path = implode('|', $path);

        $regexp = '^(' . $path . ')(\/|\/.*|\/?\?.*|\/?#.*)?$';

        return $regexp;
    }

    /**
     * Normalizes HTTP request path by trimming slashes and prepending one.
     *
     * @param string $path HTTP request path
     *
     * @return string Normalized HTTP request path
     */
    protected function normalizeHttpRequestPath($path)
    {
        $path = trim($path);

        if (empty($path)) {
            $path = '';
        } else {
            $path = '/' . trim($path, '\\/');
        }

        return $path;
    }

    public function generateStorefrontCondition(Storefront $storefront)
    {
        $condition = array();

        if ($storefront->http_host) {
            $http_condition = array();

            $http_condition[] = "req.http.host == \"{$storefront->http_host}\"";

            if (!empty($storefront->http_path)) {
                $http_condition[] = "req.url ~ \"{$this->getHttpRequestPathMatchRegexp($storefront->http_path)}\"";
            }

            $condition[] = implode(' && ', $http_condition);
        }

        if ($storefront->https_host) {
            $https_condition = array();

            $https_condition[] = "req.http.host == \"{$storefront->https_host}\"";

            if (!empty($storefront->https_path)) {
                $https_condition[] = "req.url ~ \"{$this->getHttpRequestPathMatchRegexp($storefront->https_path)}\"";
            }

            $condition[] = implode(' && ', $https_condition);
        }


        if (sizeof($condition) == 1) {
            $condition = reset($condition);
        } else {
            $condition = array_map(function ($cond_part) {
                return "({$cond_part})";
            }, $condition);

            $condition = implode(' || ', $condition);
        }


        $vcl = <<<VCL
if ({$condition}) {
    # Storefront with ID = {$storefront->id}

    if (req.http.Cookie ~ "{$storefront->session_cookie_name}=") {
        set req.http.X-Has-Session = true;
    }
}
VCL;

        return $vcl;
    }
}
