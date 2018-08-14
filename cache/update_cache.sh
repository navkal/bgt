# Copyright 2018 BACnet Gateway.  All rights reserved.
#
# Continuously update cache of Building Monitor data
#
# To run this script every midnight, edit the root crontab:
#   sudo crontab -e
#
# To start process daily at 1:00 AM, enter this line:
#   0 1 * * * sh /opt/nav/bgt/cache/update_cache.sh > /opt/nav/bgt_db/update_cache.log
#

# Set working directory
cd /opt/nav/bgt/cache

# Start cache updater
/home/ea/anaconda3/bin/python ./update_cache.py -h localhost -p 8000 -s 5
