#!/usr/bin/env bash

PROJECT_NAME="readis"

# link the uploaded nginx config to enable
echo -e "\e[0m--"
rm -rf /etc/nginx/sites-enabled/*
for name in dist; do
    # link
    ln -sf "/etc/nginx/sites-available/$name" "/etc/nginx/sites-enabled/020-$name"
    # check link
    test -L "/etc/nginx/sites-enabled/020-$name" && echo -e "\e[0mLinking nginx $name config: \e[1;32mOK\e[0m" || echo -e "Linking nginx $name config: \e[1;31mFAILED\e[0m";
done

# set correct permissions for private key
chmod 0700 /root/.ssh
chmod 0600 /root/.ssh/id_rsa
chmod 0600 /root/.ssh/config

# restart nginx
echo -e "\e[0m--"
service nginx restart

# Restart php5-fpm
service php5-fpm restart

# Determine the public ip address and show a message
IP_ADDR=`ifconfig eth1 | grep inet | grep -v inet6 | awk '{print $2}' | cut -c 6-`

echo -e "\e[0m--\nYour machine's ip address is: \e[1;31m$IP_ADDR\e[0m\n--\n"
