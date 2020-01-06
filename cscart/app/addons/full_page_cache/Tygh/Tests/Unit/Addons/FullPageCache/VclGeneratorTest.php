<?php
namespace Tygh\Tests\Unit\Addons\FullPageCache;

use Tygh\Addons\FullPageCache\Storefront;
use Tygh\Addons\FullPageCache\Varnish\VclGenerator;

class VclGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testNonCachedExtensionGetSet()
    {
        $generator = new VclGenerator();

        $exts = array('gif', 'png', 'jpeg');
        $generator->addNonCachedExtensions($exts);
        $this->assertEquals($exts, $generator->getNonCachedExtensions());

        $more_exts = array('wav', 'mp3');
        $generator->addNonCachedExtensions($more_exts);
        $this->assertEquals(array('gif', 'png', 'jpeg', 'wav', 'mp3'), $generator->getNonCachedExtensions());
    }

    public function testNonCachedPathsGetSet()
    {
        $generator = new VclGenerator();

        $paths = array('/1.gif', '1.png', '1.jpeg');
        $generator->addNonCachedPaths($paths);
        $this->assertEquals($paths, $generator->getNonCachedPaths());

        $more_paths = array('1.wav', '1.mp3');
        $generator->addNonCachedPaths($more_paths);
        $this->assertEquals(array('/1.gif', '1.png', '1.jpeg', '1.wav', '1.mp3'), $generator->getNonCachedPaths());
    }

    public function testNonCachedDispatchesGetSet()
    {
        $generator = new VclGenerator();

        $dispatches = array('checkout.cart', 'categories.view');
        $generator->addNonCachedDispatches($dispatches);
        $this->assertEquals($dispatches, $generator->getNonCachedDispatches());

        $more_dispatches = array('products.view', 'products.manage');
        $generator->addNonCachedDispatches($more_dispatches);
        $this->assertEquals(array('checkout.cart', 'categories.view', 'products.view', 'products.manage'),
            $generator->getNonCachedDispatches());
    }

    public function testCacheTtlGetSet()
    {
        $generator = new VclGenerator();

        $generator->setCacheTTL(0);
        $this->assertEquals(0, $generator->getCacheTTL());

        $generator->setCacheTTL(300);
        $this->assertEquals(300, $generator->getCacheTTL());
    }

    /**
     * @dataProvider dpGetHttpRequestPathMatchRegexp
     */
    public function testGetHttpRequestPathMatchRegexp($requested_path, $storefront_path, $does_match)
    {
        $generator = new VclGenerator();

        $regexp = '/' . $generator->getHttpRequestPathMatchRegexp($storefront_path) . '/';

        if ($does_match) {
            $this->assertRegExp($regexp, $requested_path);
        } else {
            $this->assertNotRegExp($regexp, $requested_path);
        }
    }

    public function dpGetHttpRequestPathMatchRegexp()
    {
        return array(
            array('/ru/electronics/?foo=bar', '', true),
            array('/store/', '/store', true),
            array('/store', 'store', true),
            array('/store2', 'store', false),
            array('/electronics/phones', '/electronics', true),
            array('/electronics?foo=bar&arg=val', '/electronics', true),
            array('/electronics/?foo=bar&arg=val', '/electronics', true),
            array('/electronics#anchor', '/electronics', true),
            array('/electronics/#anchor', '/electronics', true),
            array('/electronics/#anchor', array('electronics', 'electronics/phones'), true),
            array('/store2/', '/store', false),
            array('/store2/?foo=bar', '/store', false),
            array('/store2?foo=bar', '/store', false),
            array('/store2#anchor', '/store', false),
            array('/store/', '/otherstore', false),
        );
    }

    public function testGenerateStorefrontsCondition()
    {
        $generator = new VclGenerator();

        $storefront = new Storefront();

        $storefront->id = 1;
        $storefront->http_host = 'cscart.dev';
        $storefront->http_path = '/path/to/cart';
        $storefront->https_host = 'secure.cscart.dev';
        $storefront->https_path = null;
        $storefront->session_cookie_name = 'fpc_customer_0337';


        $vcl = <<<VCL
if ((req.http.host == "cscart.dev" && req.url ~ "^(\/path\/to\/cart)(\/|\/.*|\/?\?.*|\/?#.*)?$") || (req.http.host == "secure.cscart.dev")) {
    # Storefront with ID = 1

    if (req.http.Cookie ~ "fpc_customer_0337=") {
        set req.http.X-Has-Session = true;
    }
}
VCL;

        $this->assertEquals($vcl, $generator->generateStorefrontCondition($storefront));


        $storefront->id = 28;
        $storefront->http_host = 'cscart.dev';
        $storefront->http_path = '/even/more/nested/path/to/the/cart';
        $storefront->https_host = null;
        $storefront->https_path = null;
        $storefront->session_cookie_name = 'fpc_customer_0337';

        $vcl = <<<VCL
if (req.http.host == "cscart.dev" && req.url ~ "^(\/even\/more\/nested\/path\/to\/the\/cart)(\/|\/.*|\/?\?.*|\/?#.*)?$") {
    # Storefront with ID = 28

    if (req.http.Cookie ~ "fpc_customer_0337=") {
        set req.http.X-Has-Session = true;
    }
}
VCL;

        $this->assertEquals($vcl, $generator->generateStorefrontCondition($storefront));


        $storefront->id = 28;
        $storefront->http_host = 'cscart.dev';
        $storefront->http_path = null;
        $storefront->https_host = null;
        $storefront->https_path = null;
        $storefront->session_cookie_name = 'fpc_customer_0337';

        $vcl = <<<VCL
if (req.http.host == "cscart.dev") {
    # Storefront with ID = 28

    if (req.http.Cookie ~ "fpc_customer_0337=") {
        set req.http.X-Has-Session = true;
    }
}
VCL;

        $this->assertEquals($vcl, $generator->generateStorefrontCondition($storefront));


        $storefront->id = 28;
        $storefront->http_host = 'cscart.dev';
        $storefront->http_path = null;
        $storefront->https_host = 'secure.cscart.dev';
        $storefront->https_path = null;
        $storefront->session_cookie_name = 'fpc_customer_0337';

        $vcl = <<<VCL
if ((req.http.host == "cscart.dev") || (req.http.host == "secure.cscart.dev")) {
    # Storefront with ID = 28

    if (req.http.Cookie ~ "fpc_customer_0337=") {
        set req.http.X-Has-Session = true;
    }
}
VCL;

        $this->assertEquals($vcl, $generator->generateStorefrontCondition($storefront));
    }
}