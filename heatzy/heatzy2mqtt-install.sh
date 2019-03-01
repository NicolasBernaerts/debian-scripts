#!/bin/bash

# install mosquitto clients (subscriber and publisher)
apt install mosquitto-clients

# install gateway service
wget -O /usr/local/sbin/heatzy2mqtt https://raw.githubusercontent.com/NicolasBernaerts/debian-scripts/master/heatzy/heatzy2mqtt
chmod +x /usr/local/sbin/heatzy2mqtt

# declare service
wget -O /etc/systemd/system/heatzy2mqtt.service https://raw.githubusercontent.com/NicolasBernaerts/debian-scripts/master/heatzy/heatzy2mqtt.service
