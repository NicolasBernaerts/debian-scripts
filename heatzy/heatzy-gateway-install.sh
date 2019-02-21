#!/bin/bash

# install gateway service
wget -O /usr/local/sbin/heatzy-mqtt-gateway https://raw.githubusercontent.com/NicolasBernaerts/debian-scripts/master/heatzy/heatzy-mqtt-gateway
chmod +x /usr/local/sbin/heatzy-mqtt-gateway

# declare service
wget -O /etc/systemd/system https://raw.githubusercontent.com/NicolasBernaerts/debian-scripts/master/heatzy/heatzy-mqtt-gateway.service

# enable the gateway service
systemctl enable heatzy-mqtt-gateway.service
