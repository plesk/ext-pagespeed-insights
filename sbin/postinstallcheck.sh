#!/bin/bash -e

if [ ! -f /usr/local/psa/var/modules/pagespeed-insights/removepagespeed ]
then
    if [ -f /etc/redhat-release ];
    then
        if [ $(rpm -qa | grep -c "pagespeed") -eq 0 ];
        then
            echo "1" > "/usr/local/psa/var/modules/pagespeed-insights/removepagespeed"
        else
            echo "0" > "/usr/local/psa/var/modules/pagespeed-insights/removepagespeed"
        fi
    else
        if [ $(dpkg --get-selections | grep -c pagespeed) -eq 0 ];
        then
            echo "1" > "/usr/local/psa/var/modules/pagespeed-insights/removepagespeed"
        else
            echo "0" > "/usr/local/psa/var/modules/pagespeed-insights/removepagespeed"
        fi
    fi
fi

exit 0