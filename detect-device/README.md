Device2MQTT
===========

This script should be run as a service.
It detects all new devices on a LAN which provides a mDNS name.
It publishes new devices and lost device thru MQTT.
That allow a very simple presence detection thru smartphone for example.

To install, download files to :
  * /usr/local/bin/device2mqtt
  * /etc/systemd/system/device2mqtt.service

Then run these commands to declare the service :

    systemctl daemon-reload
    systemctl enable device2mqtt.service
    systemctl start device2mqtt.service

To disable the service :

    systemctl stop device2mqtt.service
    systemctl disable device2mqtt.service
