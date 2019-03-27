#!/bin/bash
# zoneminder camera wall installation script
# installation directory is given as only parameter

# go to installation directory
cd $1

# download files
wget https://raw.githubusercontent.com/NicolasBernaerts/debian-scripts/master/zoneminder/cam-config.inc
wget https://raw.githubusercontent.com/NicolasBernaerts/debian-scripts/master/zoneminder/cam-status.php
wget https://raw.githubusercontent.com/NicolasBernaerts/debian-scripts/master/zoneminder/cam-image.jpeg.php
wget https://raw.githubusercontent.com/NicolasBernaerts/debian-scripts/master/zoneminder/cam-wall.php

# download and extract fancy zoom library
wget https://github.com/NicolasBernaerts/debian-scripts/raw/master/zoneminder/js-global.zip
unzip js-global.zip
rm js-global.zip

# set owner as www-data
chown -R www-data:www-data *
