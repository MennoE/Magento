#!/bin/sh

# Set rights for default Magento installation
chmod 777 app/etc/

chmod 777 var/
chmod 777 var/package -R

chmod 777 media/
chmod 777 media/downloadable
chmod 777 media/customer

chmod 777 media/xmlconnect -R

# Add log directory
mkdir var/log
chmod 777 var/log


