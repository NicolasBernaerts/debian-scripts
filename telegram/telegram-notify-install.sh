#!/bin/bash

# install packages
apt-get install curl

# retrieve configuration file and main script
wget -O /etc/telegram-notify.conf https://raw.githubusercontent.com/NicolasBernaerts/debian-scripts/master/telegram/telegram-notify.conf
wget -O /usr/local/bin/telegram-notify https://raw.githubusercontent.com/NicolasBernaerts/debian-scripts/master/telegram/telegram-notify
chmod +x /usr/local/bin/telegram-notify

# retrieve rsync wrapper
wget -O /usr/local/bin/rsync https://raw.githubusercontent.com/NicolasBernaerts/debian-scripts/master/telegram/rsync
chmod +x /usr/local/bin/rsync
