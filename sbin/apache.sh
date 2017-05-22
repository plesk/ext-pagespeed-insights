#!/bin/bash -e

### Add the PageSpeed repository and install the Apache module
if [ -f /etc/redhat-release ]
then
    wget https://dl-ssl.google.com/dl/linux/direct/mod-pagespeed-stable_current_x86_64.rpm
    if [ $(rpm -qa | grep -c "pagespeed") -eq 0 ];
    then
        yum -y -q install at
        yum -y -q install mod-pagespeed-*.rpm
    else
        yum -y -q reinstall at
        yum -y -q reinstall mod-pagespeed-*.rpm
    fi

    service httpd restart
else
    wget https://dl-ssl.google.com/dl/linux/direct/mod-pagespeed-stable_current_amd64.deb
    dpkg -i mod-pagespeed-*.deb
    apt-get -qq -y -f install

    service apache2 restart
fi

exit 0