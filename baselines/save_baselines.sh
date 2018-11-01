# Copyright 2018 Building Monitor.  All rights reserved.
#
# Save new baseline in database
#
# To run this script every midnight, edit the root crontab:
#   sudo crontab -e
#
# Enter this line:
#   0 0 * * * sh /opt/nav/bgt/baselines/save_baselines.sh > /opt/nav/bgt_db/save_baselines.log
#

# Set working directory
cd /opt/nav/bgt/baselines

# Save new baseline
/home/ea/anaconda3/bin/python ./save_baselines.py -h localhost -p 8000
