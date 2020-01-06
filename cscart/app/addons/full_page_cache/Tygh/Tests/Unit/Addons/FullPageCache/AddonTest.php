<?php
namespace Tygh\Tests\Unit\Addons\FullPageCache;

use Tygh\Addons\FullPageCache\Addon;

class AddonTest extends \PHPUnit_Framework_TestCase
{
    public function testBuildBanByTagsRegexp()
    {
        $app = $this->getMock('\Tygh\Application');

        $addon = new Addon($app, array());
        $addon->setCacheTagsHttpHeaderName('X-Cache-Tags');

        $tags = array('qwe', 'asd', 'foo', 'bar', 'bar');

        $this->assertEquals(
            'obj.http.X-Cache-Tags ~ "qwe|asd|foo|bar"',
            $addon->buildBanByTagsRegexp($tags)
        );
    }

    public function testGetCacheTagsHeader()
    {
        $app = $this->getMock('\Tygh\Application');

        $addon = new Addon($app, array());
        $addon->setCacheTagsHttpHeaderName('X-Cache-Tags');

        $tags = array('qwe', 'asd', 'foo', 'bar', 'bar');

        $addon->registerCacheTags($tags);

        // 7 first symbols of SHA-1 hashes
        $this->assertEquals('X-Cache-Tags: 056eafe,f10e282,0beec7b,62cdb70', $addon->getPageCacheTagsHeader());
    }

    public function testRenderEsiForBlock()
    {
        $app = $this->getMock('\Tygh\Application');

        $addon = new Addon($app, array());

        $this->assertEquals(
            '<!-- ESI render URL: http://example.com/store/esi.php?block_id=120&snapping_id=125&lang_code=RU --><esi:include src="http://example.com/store/esi.php?block_id=120&snapping_id=125&lang_code=RU" /><esi:remove><div id="block12"><h1>Hello, world!</h1></div></esi:remove>',
            $addon->renderESIForBlock(
                array(
                    'block_id' => 120,
                    'snapping_id' => 125
                ),
                '<div id="block12"><h1>Hello, world!</h1></div>',
                'RU', 'http://example.com/store//', true
            )
        );

        $this->assertEquals(
            '<esi:include src="http://example.com/store/esi.php?block_id=120&snapping_id=125&lang_code=RU" /><esi:remove><div id="block12"><h1>Hello, world!</h1></div></esi:remove>',
            $addon->renderESIForBlock(
                array(
                    'block_id' => 120,
                    'snapping_id' => 125
                ),
                '<div id="block12"><h1>Hello, world!</h1></div>',
                'RU', 'http://example.com/store//', false
            )
        );
    }
}