# LiteSpeed caching addon for CS-Cart (BETA)


## Installation

* This addon is still in beta and under active development.

* This addon has been tested with CSCart 4.11.2. It should work on both older and newer versions, but you may run into some issues.

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

2. 

*For more information, please refer to the "CSCart Manage Add-ons" screenshot at the bottom.*

## Support

If you find any issues, bugs, or possible improvements - please open an issue on Github. Be sure to mention the relevant stack and versions that you're using.

## Screenshots

### LSWS Cache configuration

![LSWS Cache configuration](https://s.woet.me/GuqWQZhHx7.png)

### CSCart Manage Add-ons

![CSCart Manage Add-ons](https://s.woet.me/OZCWQq8kf7.png)