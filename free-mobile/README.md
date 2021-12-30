# Publish your Free mobile data thru MQTT

This simple script **free-mobile-mqtt** allows you to publish your Free Mobile meters (call, SMS, MMS and Data) on any MQTT broker.
It allows to get almost real time values without any need to connect to your account.
It has been tested on Debian and Ubuntu.

This script needs :
  * git
  * jq
  * pup (from https://github.com/ericchiang/pup)

Here is how to install them on Debian :
```
# apt install git jq golang-go
# go get github.com/ericchiang/pup
# mv ~/go/bin/pup /usr/local/bin 
```

You can now place the script under **/etc/cron-hourly/free-mobile-mqtt**
It will run every hour and publish your accout status.

If everything works as expected, your Free Mobile account data should be published on your MQTT broker :
```
topic/of/your/account {"Line":"07 02 03 04 05"}
topic/of/your/account {"local":{"Appel":{"Emis":"0s","Recu":"0s","Hors":"0.00€"},"SMS":{"Emis":"0","Hors":"0.00€"},"MMS":{"Emis":"0","Hors":"0.00€"},"Data":{"Total":"18,51Go","Hors":"0.00€"}}}
topic/of/your/account {"roaming":{"Appel":{"Emis":"0s","Recu":"0s","Hors":"0.00€"},"SMS":{"Emis":"0","Hors":"0.00€"},"MMS":{"Emis":"0","Hors":"0.00€"},"Data":{"Total":"0o","Hors":"0.00€"}}}
```

Here is what I get from openHab2 :

![OpenHab](https://github.com/NicolasBernaerts/debian-scripts/raw/master/free-mobile/openhab.png) 
