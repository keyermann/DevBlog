#!/bin/sh

sudo setfacl -R -m u:www-data:rwx -m u:$USER:rwx app/cache app/logs app/Resources
sudo setfacl -dR -m u:www-data:rwx -m u:$USER:rwx app/cache app/logs app/Resources
sudo chmod 777 -R app/Resources
sudo chmod 777 app/config/parameters.yml
