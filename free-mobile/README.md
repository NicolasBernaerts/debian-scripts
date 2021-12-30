# Publish your Free mobile data thru MQTT

This simple script allows you to publish your Free Mobile meters (call, SMS, MMS and Data) on any MQTT broker.
This allows me to get almost real time values without any need to connect to my account.
It has been tested on Debian and Ubuntu

This script needs :
  * git
  * jq
  * pup

Here is how to install them on Debian :
```
# apt install git jq golang-go
# go get github.com/ericchiang/pup
# mv ~/go/bin/pup /usr/local/bin 
```

If everything works as expected, your Free Mobile account data should be published on your MQTT broker :
```
topic/of/your/account {"Line":"07 02 03 04 05"}
topic/of/your/account {"local":{"Appel":{"Emis":"0s","Recu":"0s","Hors":"0.00€"},"SMS":{"Emis":"0","Hors":"0.00€"},"MMS":{"Emis":"0","Hors":"0.00€"},"Data":{"Total":"18,51Go","Hors":"0.00€"}}}
topic/of/your/account {"roaming":{"Appel":{"Emis":"0s","Recu":"0s","Hors":"0.00€"},"SMS":{"Emis":"0","Hors":"0.00€"},"MMS":{"Emis":"0","Hors":"0.00€"},"Data":{"Total":"0o","Hors":"0.00€"}}}
```
