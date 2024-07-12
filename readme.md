<!--
SPDX-License-Identifier: EUPL-1.2
-->

# MQTT forward of Airrohr data

* configure a domain 
* create a vhost or container with php/webserver/ssl certificate on your server for the domain
* create data/ dir in webroot with write access for webserver
* configure mqtt parameters 
* run and debug until it works :)

```bash
wget https://getcomposer.org/installer
php installer --install-dir=bin --filename=composer
php bin/composer require php-mqtt/client

if [ ! -d "data" ] ; then
# create data dir, set permissions
mkdir -p data
chmown root/www-data data
chmod ug+rwx data
chmod o-w data
fi
if [ ! -f "config.mqtt.php" ] ; then
cp config.mqtt.php.template config.mqtt.php
fi
```

sources used to assemble this:
* [madavi-api data\_simple.php airrohr sensor data API example](https://github.com/opendata-stuttgart/madavi-api/blob/master/data_simple.php) License not stated, but [recommended as starting point by author](https://forum.sensor.community/t/how-to-send-to-own-api/975/4), and other works like [the corresponding firmware]()
* [php-mqtt/client library's](https://github.com/php-mqtt/client) example code [01\_authorize\_with\_username\_and\_password.php](https://github.com/php-mqtt/client-examples/blob/master/03_connection_settings/01_authorize_with_username_and_password.php), both [MIT](https://github.com/php-mqtt/client/blob/master/LICENSE.md) [license](https://github.com/php-mqtt/client-examples/blob/master/LICENSE.md)

The european Joinup Licensing Assistant [compatibility checker](https://joinup.ec.europa.eu/collection/eupl/solution/joinup-licensing-assistant/jla-compatibility-checker) states, that the permissive [MIT license allows redistribution under other licenses](https://joinup.ec.europa.eu/licence/compatibility-check/MIT/EUPL-1.2), so here [EUPL-1.2](LICENSES/EUPL-1.2.txt) is our choice.

Licenses handled and added via the great [reuse](https://reuse.software/) tool, thanks to all contributors of openly licensed source code and infrastucture!
Shoutout for great dev/debug sessions with Alex.

Such a functionality seems to have been already implemented elsewhere at [jklmnn/airrohr-mqtt](https://github.com/jklmnn/airrohr-mqtt) for Homeassistant.
