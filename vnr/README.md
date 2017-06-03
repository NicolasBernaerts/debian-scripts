This small PHP script provides a simple way to get a Video Camera Wall from a simple web browser.

Video camera list is in file **cam-wall.lst**. Parameters are :

**camera descrition;image URL;list of wall numbers**

You can define different camera walls in this configuration file.

Script is having to different display options :
  * wall only : You get big thumbnails of all camera associated with the wall number. CSS file is **cam-wall-only.inc**.
  * wall & cam : You get small thumbnails of all camera and a camera picture in detail. CSS file is **cam-wall-detail.inc**.

When you call the script, you can use few arguments :
  * wall=xx, where **xx** is the wall number (camera associated in **cam-wall.lst**)
  * cam=yy, where **yy** is the detail camera index
  * rate=zzzz, where **zzzz** is the delay in milliseconds between two display refresh for the camera on the wall 
