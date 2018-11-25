#!/bin/bash

# if absent, create config directory
mkdir --parents $HOME/.config

# retrieve configuration file
wget -O $HOME/.config/heatzy.conf https://raw.githubusercontent.com/NicolasBernaerts/debian-scripts/master/heatzy/heatzy.conf

# install main script
sudo wget -O /usr/local/sbin/heatzy https://raw.githubusercontent.com/NicolasBernaerts/debian-scripts/master/heatzy/heatzy
sudo chmod +x /usr/local/sbin/heatzy
