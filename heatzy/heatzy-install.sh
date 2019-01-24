#!/bin/bash

# if absent, create config directory
mkdir --parents $HOME/.config

# install icon
sudo wget -O /usr/share/icons/heatzy-pilote.png https://raw.githubusercontent.com/NicolasBernaerts/icon/master/heatzy-pilote.png

# retrieve configuration file
wget -O $HOME/.config/heatzy.conf https://raw.githubusercontent.com/NicolasBernaerts/debian-scripts/master/heatzy/heatzy.conf

# install heatzy script
sudo wget -O /usr/local/sbin/heatzy https://raw.githubusercontent.com/NicolasBernaerts/debian-scripts/master/heatzy/heatzy
sudo chmod +x /usr/local/sbin/heatzy

# install heatzy-gui script
sudo wget -O /usr/local/sbin/heatzy-gui https://raw.githubusercontent.com/NicolasBernaerts/debian-scripts/master/heatzy/heatzy-gui
sudo chmod +x /usr/local/sbin/heatzy-gui

# desktop integration
sudo wget -O /usr/share/applications/heatzy.desktop https://raw.githubusercontent.com/NicolasBernaerts/debian-scripts/master/heatzy/heatzy.desktop
sudo chmod +x /usr/share/applications/heatzy.desktop
