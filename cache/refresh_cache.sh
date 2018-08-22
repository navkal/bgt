# Copyright 2018 BACnet Gateway.  All rights reserved.
#
# Continuously update cache of Building Monitor data
#
# To run this script periodically, edit the root crontab:
#   sudo crontab -e
#
# To restart process every 5 minutes, enter this line:
#   */5 * * * * sh /opt/nav/bgt/cache/refresh_cache.sh
#

# Set working directory
cd /opt/nav/bgt/cache

# Start cache updater
/home/ea/anaconda3/bin/python ./refresh_cache.py -h localhost -p 8000 -s 5
