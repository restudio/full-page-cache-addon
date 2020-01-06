# LiteSpeed caching addon for CS-Cart (BETA)

## Important notes

* This addon is still in beta and under active development.

* This addon has been tested with CSCart 4.11.2. It should work on both older and newer versions, but you may run into some issues.

* This addon requires the use of ESI. OpenLiteSpeed does not support ESI at this time.

## Installation

### Configuring LiteSpeed Web Server

* Please make sure that you have a "Storage Path" configured at either a server or virtual host level.

* All other settings should either be "Not Set" or configured appropriately.

* Note that virtual hosts settings override any server settings.

*For more information, please refer to the "LSWS Cache configuration" screenshot at the bottom.*

### Installing the addon

1. Clone the Github repo

```
git clone https://github.com/litespeedtech/full-page-cache-addon
```

2. Move the contents of `cscart/*` to your CSCart installation
```
mv full-page-cache-addon/cscart/* /your/cscart/installation/
```

3. Append the contents of `htaccess_lscache` to your `.htaccess`
```
cat full-page-cache-addon/htaccess_lscache >> /your/cscart/installation/.htaccess
```

### Configuring CSCart

1. Navigate to `Add-ons -> Manage add-ons` at the right top of the CSCart admin panel.

2. Scroll down to the `Full-page cache` add-on and click `Install`.

3. Scroll down to the `Full-page cache` add-on again, click `Disabled` and change it to `Active`.

*For more information, please refer to the "CSCart Manage Add-ons" screenshot at the bottom.*

### Testing

After following the steps above, check the response headers of your requests to CSCart. You should see this in the initial request:

`X-LiteSpeed-Cache: miss`

And after you refresh, it should change to a hit:

`X-LiteSpeed-Cache: hit`

## Support

If you find any issues, bugs, or possible improvements - please open an issue on Github. Be sure to mention the relevant stack and versions that you're using.

## Screenshots

### LSWS Cache configuration

![LSWS Cache configuration](https://s.woet.me/GuqWQZhHx7.png)

### CSCart Manage Add-ons

![CSCart Manage Add-ons](https://s.woet.me/OZCWQq8kf7.png)

![CSCart Install Add-on](https://s.woet.me/KwgyYktCfQ.png)

![CSCart Activate Add-on](https://s.woet.me/Yn7QgiDMo3.png)