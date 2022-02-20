**Telegram**

These scripts are used to send Telegram messages from a Debian or Ubuntu server.

Usage information is available at http://www.bernaerts-nicolas.fr/linux/75-debian/351-debian-send-telegram-notification

The script **rsync** should be placed under **/usr/local/bin** to be used as a rsync caller. It handles a new option **--telegram** which allows to send a **success** or **error** telegram notification once the rsync synchronisation is over.
