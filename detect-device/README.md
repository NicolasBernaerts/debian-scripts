Device2MQTT
===========

This script should be run as a service.
It detects all new devices on a LAN which provides a mDNS name.
It publishes new devices and lost device thru MQTT.
That allow a very simple presence detection thru smartphone for example.

To install :

    wget -O /usr/local/bin/device2mqtt https://raw.githubusercontent.com/NicolasBernaerts/debian-scripts/refs/heads/master/detect-device/device2mqtt
    chmod +x /usr/local/bin/device2mqtt
    wget -O /etc/systemd/system/device2mqtt.service https://raw.githubusercontent.com/NicolasBernaerts/debian-scripts/refs/heads/master/detect-device/device2mqtt.service

Then run these commands to declare the service :

    systemctl daemon-reload
    systemctl enable device2mqtt.service
    systemctl start device2mqtt.service

To disable the service :

    systemctl stop device2mqtt.service
    systemctl disable device2mqtt.service
