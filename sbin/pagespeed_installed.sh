#!/bin/bash -e

if [ -f /etc/redhat-release ];
then
    if [ $(rpm -qa | grep -c "pagespeed") -eq 0 ];
    then
        echo "0"
    else
        echo "1"
    fi
else
    if [ $(dpkg --get-selections | grep -c pagespeed) -eq 0 ];
    then
        echo "0"
    else
        echo "1"
    fi
fi

exit 0