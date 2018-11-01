# Copyright 2018 Building Monitor.  All rights reserved.
#
# Continuously update cache of Building Monitor data
#
# To run this script periodically, edit the root crontab:
#   sudo crontab -e
#
# To restart process every 5 minutes, enter this line:
#   */5 * * * * sh /opt/nav/bgt/cache/load_cache.sh
#

# Set working directory
cd /opt/nav/bgt/cache

# Start cache updater
/home/ea/anaconda3/bin/python ./load_cache.py -h localhost -p 8000 -s 5
