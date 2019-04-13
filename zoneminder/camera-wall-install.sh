#!/bin/bash
# zoneminder camera wall installation script
# installation directory is given as only parameter

# go to installation directory
cd $1

# download files
wget https://raw.githubusercontent.com/NicolasBernaerts/debian-scripts/master/zoneminder/camera-config.inc
wget https://raw.githubusercontent.com/NicolasBernaerts/debian-scripts/master/zoneminder/camera-status.php
wget https://raw.githubusercontent.com/NicolasBernaerts/debian-scripts/master/zoneminder/camera-image.jpeg.php
wget https://raw.githubusercontent.com/NicolasBernaerts/debian-scripts/master/zoneminder/camera-wall.php

# download and extract fancy zoom library
wget https://github.com/NicolasBernaerts/debian-scripts/raw/master/zoneminder/js-global.zip
unzip js-global.zip
rm js-global.zip

# set owner as www-data
chown -R www-data:www-data *

# configuration message
echo "You should now configure camera-config.inc according to your ZoneMinder setup."
