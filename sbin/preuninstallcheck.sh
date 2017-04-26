#!/bin/bash -e

REMOVEPAGESPEED=`cat /usr/local/psa/var/modules/pagespeed-insights/removepagespeed`

if [ "$REMOVEPAGESPEED" = "1" ];
then
    if [ -f /etc/redhat-release ];
    then
        if [ $(rpm -qa | grep -c "pagespeed") -eq 1 ];
        then
            PAGESPEED=$(rpm -qa | grep "pagespeed")

            if [ ! -z $PAGESPEED ];
            then
                yum -y -q remove $PAGESPEED
            fi

            AT=$(rpm -qa | grep "^at-")

            if [ ! -z $AT ];
            then
                yum -y -q remove $AT
            fi

            service httpd restart
        fi
    else
        if [ $(dpkg --get-selections | grep -c pagespeed) -eq 1 ];
        then
            apt-get -qq -y --purge autoremove mod-pagespeed-stable

            if [ -f /etc/apt/sources.list.d/mod-pagespeed.list ]
            then
                rm /etc/apt/sources.list.d/mod-pagespeed.list
            fi

            service apache2 restart
        fi
    fi
fi

exit 0