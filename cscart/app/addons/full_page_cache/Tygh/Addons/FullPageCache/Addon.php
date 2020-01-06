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

use ride\library\varnish\exception\VarnishException;
use ride\library\varnish\VarnishAdmin;
use Tygh\Addons\FullPageCache\Varnish\VclGenerator;
use Tygh\Application;

final class Addon
{
    /**
     * Cache-enabling VCL file name
     */
    const ENABLING_VCL_FILE_NAME = 'enabled.vcl';

    /**
     * Cache-disabling VCL file name
     */
    const DISABLING_VCL_FILE_NAME = 'disabled.vcl';

    /**
     * @var \Tygh\Application Instance of Application container.
     */
    protected $app;

    /**
     * @var array Add-on settings list.
     */
    protected $settings;

    /**
     * @var string Path to directory containing VCL files.
     */
    protected $varnish_vcl_directory;

    /**
     * @var string Name of HTTP header that will contain page cache tags list.
     */
    protected $cache_tags_http_header_name = 'X-Cache-Tags';

    /**
     * @var array List of registered cache tags for the current page.
     */
    protected $page_cache_tags = array();

    /**
     * @var is using LiteSpeed Cache.
     */
    protected $is_lscache = 1;
    
    /**
     * Addon constructor.
     *
     * @param \Tygh\Application $app
     * @param                   $settings
     */
    public function __construct(Application $app, $settings)
    {
        $this->app = $app;
        $this->setSettings($settings);
        
        if ($this->is_lscache) {
            $this->cache_tags_http_header_name = "X-LiteSpeed-Tag";
        }
    }

    /**
     * @param array $settings Add-on settings list.
     */
    public function setSettings($settings)
    {
        $this->settings = $settings;
    }

    /**
     * @param string $varnish_vcl_directory Path to directory containing VCL files.
     */
    public function setVarnishVCLDirectory($varnish_vcl_directory)
    {
        $this->varnish_vcl_directory = rtrim(realpath($varnish_vcl_directory), '\\/');
    }

    /**
     * @return string Path to directory containing VCL files without trailing slashes.
     */
    public function getVarnishVCLDirectory()
    {
        return $this->varnish_vcl_directory;
    }

    /**
     * @return string Path to cache-enabling VCL file.
     */
    public function getEnablingVCLFilePath()
    {
        return $this->varnish_vcl_directory . DIRECTORY_SEPARATOR . static::ENABLING_VCL_FILE_NAME;
    }

    /**
     * @return string Path to cache-disabling VCL file.
     */
    public function getDisablingVCLFilePath()
    {
        return $this->varnish_vcl_directory . DIRECTORY_SEPARATOR . static::DISABLING_VCL_FILE_NAME;
    }

    /**
     * @return string Name of HTTP header that will contain page cache tags list.
     */
    public function getCacheTagsHttpHeaderName()
    {
        return $this->cache_tags_http_header_name;
    }

    /**
     * @param string $cache_tags_http_header_name Name of HTTP header that will contain page cache tags list.
     */
    public function setCacheTagsHttpHeaderName($cache_tags_http_header_name)
    {
        $this->cache_tags_http_header_name = $cache_tags_http_header_name;
    }

    /**
     * @return array List of cache tags for the current page.
     */
    public function getPageCacheTags()
    {
        return $this->page_cache_tags;
    }

    /**
     * Registers given list of tags for the current page.
     *
     * @param array $tags List of cache tags to register.
     */
    public function registerCacheTags(array $tags)
    {
        foreach ($tags as $table) {
            $this->page_cache_tags[] = $this->mapStringToCacheTag($table);
        }
    }

    /**
     * Creates a short hash of given value that can be used as a cache tag.
     *
     * @param string $string Value to map.
     *
     * @return string Short hash of given value.
     */
    public function mapStringToCacheTag($string)
    {
        return substr(sha1($string), 0, 7);
    }

    /**
     * Checks whether server environment is suitable for full page caching.
     *
     * @return bool Whether the addon can be enabled.
     */
    public function canBeEnabled()
    {
        $result = true;

        if (!$this->is_lscache && (!is_dir($this->varnish_vcl_directory) || !is_writable($this->varnish_vcl_directory))) {
            $result = false;
            fn_set_notification(
                'E',
                __('full_page_cache.unable_to_enable_full_page_caching'),
                __('full_page_cache.error_cant_write_to_varnish_vcl_directory', array(
                    '[directory]' => $this->varnish_vcl_directory
                ))
            );
        }

        if (ini_get('session.auto_start')) {
            $result = false;
            fn_set_notification(
                'E',
                __('full_page_cache.unable_to_enable_full_page_caching'),
                __('full_page_cache.error_session_auto_start_enabled')
            );
        }

        if (!$this->is_lscache && !$this->checkConnectionToVarnishAdm()) {
            $result = false;
            fn_set_notification(
                'E',
                __('full_page_cache.unable_to_enable_full_page_caching'),
                __('full_page_cache.unable_to_connect_to_varhish')
            );
        }

        return $result;
    }

    /**
     * Checks connection to varnishadm.
     *
     * @return bool Whether connection to the varnishadm is established and can be used.
     */
    public function checkConnectionToVarnishAdm()
    {
        try {
            $varnish_adm = $this->getVarnishAdmInstance();
            $varnish_adm->connect();
            $varnish_adm->ping();

            return true;
        } catch (VarnishException $e) {
            return false;
        }
    }

    /**
     * @return VarnishAdmin
     */
    public function getVarnishAdmInstance()
    {
        return $this->app['addons.full_page_cache.varnishadm'];
    }

    /**
     * @return VclGenerator
     */
    public function getVclGeneratorInstance()
    {
        return $this->app['addons.full_page_cache.vcl_generator'];
    }

    /**
     * This method is being called after add-on has been enabled.
     * It regenerates cache-enabling VCL file and makes Varnish use it.
     *
     * @return void
     */
    public function onAddonEnable()
    {
        if ($this->is_lscache) {
            return;
        }
        $this->regenerateEnablingVCLFile();
        $this->useEnablingVCLFile();
        $this->sendNotificationsOnEnable();
    }


    public function sendNotificationsOnEnable()
    {
        fn_set_notification(
            'N',
            __('successful'),
            __('full_page_cache.notice_caching_was_enabled'),
            'S'
        );


        /** @var Storefront $storefront */
        $storefront = $this->app['addons.full_page_cache.storefront'];

        fn_set_notification(
            'W',
            __('warning'),
            __('full_page_cache.warning_cache_works_only_for_storefront', array('[storefront]' => $storefront->name)),
            'S'
        );
    }

    /**
     * This method is being called after add-on has been disabled.
     * It makes Varnish use the cache-disabling VCL file.
     *
     * @return void
     */
    public function onAddonDisable()
    {
        if ($this->is_lscache){
            return;
        }
        $this->useDisablingVCLFile();
    }

    /**
     * Regenerates enabling VCL file contents using the schema and add-on settings.
     *
     * @return void
     */
    public function regenerateEnablingVCLFile()
    {
        file_put_contents($this->getEnablingVCLFilePath(), $this->getVclGeneratorInstance()->generate());
    }

    /**
     * Makes Varnish use cache-enabling VCL file.
     *
     * @return void
     */
    public function useEnablingVCLFile()
    {
        $this->useVCLFile($this->getEnablingVCLFilePath());
    }

    /**
     * Makes Varnish use cache-disabling VCL file.
     *
     * @return void
     */
    public function useDisablingVCLFile()
    {
        $this->useVCLFile($this->getDisablingVCLFilePath());
    }

    /**
     * Makes Varnish use given VCL file.
     *
     * @param string $vcl_file_path Path to VCL file.
     *
     * @throws VarnishException When an attempt to change active VCL file failed.
     *
     * @return void
     */
    public function useVCLFile($vcl_file_path)
    {
        $varnish_adm = $this->getVarnishAdmInstance();

        $vcl_list = $varnish_adm->getVclList();

        if (isset($vcl_list['boot'])) {
            $varnish_adm->useVcl('boot');
        }

        foreach ($vcl_list as $loaded_vcl => $is_enabled) {
            if ($loaded_vcl == $vcl_file_path) {
                $varnish_adm->discardVcl($loaded_vcl);

                break;
            }
        }

        $varnish_adm->loadAndUseVclFromFile($vcl_file_path, $vcl_file_path);

        $varnish_adm->stop();
        $varnish_adm->start();
    }

    /**
     * Wraps given block contents with ESI directives.
     *
     * @param array  $block         Block data
     * @param string $block_content Rendered HTML contents of the block
     * @param string $lang_code     Code of language in which block is rendered.
     * @param string $root_url      Root URL of the cart installation
     * @param string $requested_uri The URI of the requested page containing ESI directives.
     * @param bool   $debug         Whether to add debug HTML-comments above ESI directives.
     *
     * @return string ESI XML tags.
     */
    public function renderESIForBlock($block, $block_content, $lang_code, $root_url, $requested_uri, $debug = false)
    {
        $block_render_url = sprintf(
            '%s/esi.php?block_id=%u&snapping_id=%u&lang_code=%s&requested_uri=%s',
            rtrim($root_url, '\\/'),
            $block['block_id'],
            $block['snapping_id'],
            $lang_code,
            rawurlencode($requested_uri)
        );

        $return = sprintf('%s<esi:include src="%s" /><esi:remove>%s</esi:remove>',
            $debug ? "<!-- ESI render URL: {$block_render_url} -->" : '',
            $block_render_url,
            $block_content
        );

        return $return;
    }

    public function renderESIForCSRF($content){
        // Define the URL and tag
        $csrf_url = sprintf(
            '%s/esi.php?csrf=true',
            rtrim($root_url, '\\/')
        );

        $csrf_tag = '<esi:include src="'.$csrf_url.'" />';
        $csrf_tag .= '<esi:remove>'.fn_generate_security_hash().'</esi:remove>';

        // Get rid of original fields
        $content = preg_replace(
            '/<input type="hidden" name="security_hash".*?>/i', 
            '', 
            $content
        );
        
        // Replace HTML tags
        $content = str_replace(
            '</form>', 
            '<input type="hidden" name="security_hash" class="cm-no-hide-input" value="'.$csrf_tag .'" /></form>', 
            $content
        );

        // Replace HTML script
        $content = preg_replace(
            "~_.security_hash = '(.*?)';~", 
            "_.security_hash = '".$csrf_tag."';", 
            $content
        );

        return $content;
    }

    /**
     * Invalidates all Varnish cache records that are marked with given tag.
     *
     * @param string $tag Cache tag
     */
    public function invalidateByTag($tag)
    {
        $this->invalidateByTags((array) $tag);
    }

    /**
     * Invalidates all Varnish cache records that are marked with any of the given tags.
     *
     * @param array $tags List of cache tags
     */
    public function invalidateByTags(array $tags)
    {
        if (!empty($tags)) {
            if ($this->is_lscache) {
                if (!headers_sent()) {
                    $tags = implode(', tag=', $tags);
                    header( "X-LiteSpeed-Purge: tag=". $tags );
                    return;
                }
            }
            $this->getVarnishAdmInstance()->ban(
                $this->buildBanByTagsRegexp($tags)
            );
        }
    }

    /**
     * Builds an regular expression that will match Varnish cache objects against given tag list.
     *
     * @param array $tags List of cache tags
     *
     * @return string Regular expression that should be passed to varnishadm's "ban" command.
     */
    public function buildBanByTagsRegexp(array $tags)
    {
        $tags = array_unique($tags);

        $tags = implode('|', $tags);

        $regexp = 'obj.http.' . $this->cache_tags_http_header_name . ' ~ "' . $tags . '"';

        return $regexp;
    }

    /**
     * @return string HTTP header that contains cache tags for the current page.
     */
    public function getPageCacheTagsHeader()
    {
        $tags = $this->getPageCacheTags();

        $tags = array_unique($tags);

        $tags = implode(',', $tags);

        return $this->cache_tags_http_header_name . ': ' . $tags;
    }

    /**
     * @return bool Whether the current request is an ESI request.
     */
    public function isEsiRequest()
    {
        return isset($_SERVER['HTTP_X_VARNISH_ESI']) && $_SERVER['HTTP_X_VARNISH_ESI'] == 'true';
    }
}