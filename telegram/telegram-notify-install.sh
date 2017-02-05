#!/bin/bash

# install packages
apt-get install curl

# retrieve configuration file and main script
wget -O /etc/telegram-notify.conf https://raw.githubusercontent.com/NicolasBernaerts/debian-scripts/master/telegram/telegram-notify.conf
wget -O /usr/local/sbin/telegram-notify https://raw.githubusercontent.com/NicolasBernaerts/debian-scripts/master/telegram/telegram-notify
chmod +x /usr/local/sbin/telegram-notify
