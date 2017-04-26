#!/bin/bash -e

### Restart web server
if [ -f /etc/redhat-release ]
then
    service httpd restart
else
    service apache2 restart
fi

exit 0