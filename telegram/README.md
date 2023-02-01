Telegram
=======

These scripts are used to send Telegram messages from a Debian or a Ubuntu server.

Usage information is available at http://www.bernaerts-nicolas.fr/linux/75-debian/351-debian-send-telegram-notification

To install the script on your server, just :
  * download and run **telegram-notify-install.sh**
  * set your Telegram API credentials in **/etc/telegram-notify.conf**

RSYNC
-----
**rsync** script is a simple wrapper that should be placed under **/usr/local/bin**.

It adds a new option **--telegram** which allows to send a **success** or **error** telegram notification once the rsync synchronisation is over.

I use it with my Open Media Vault rsync synchronisations as OMV rsync call can not be configured.
