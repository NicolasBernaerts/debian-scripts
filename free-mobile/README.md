# Publish your Free mobile data thru MQTT

I was needing a way to follow my home Free Mobile 4G data connexion directly from my OpenHab server.

This simple script **free-mobile-mqtt** allows to do that :
  * it connects to Free Mobile using your credentials
  * it parses the page to retrieve important meter values (call, SMS, MMS and Data)
  * it publishes these values on a MQTT broker.

It has been tested on Debian and Ubuntu.

This script needs **git**, **jq** and **pup** (from https://github.com/ericchiang/pup). \
Here is how to install them on Debian :
```
# apt install git jq golang-go
# go get github.com/ericchiang/pup
# mv ~/go/bin/pup /usr/local/bin 
```

You can now place the script under **/etc/cron-hourly/free-mobile-mqtt** \
It will run every hour and publish your accout status.

If everything works as expected, your Free Mobile account data should be published on your MQTT broker :
```
topic/of/your/account {"line":"07 06 05 04 03","local":{"data":"11,61Go","call":"Illimité","sms":"Illimité","mms":"Illimité"},"roaming":{"data":"2,11Go","call":"Illimité","sms":"Illimité","mms":"Illimité"}}
```

Here is what I get from openHab2 to follow my data consumption :

![OpenHab](https://github.com/NicolasBernaerts/debian-scripts/raw/master/free-mobile/openhab.png) 
