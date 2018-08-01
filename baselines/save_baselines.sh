# Copyright 2018 BACnet Gateway.  All rights reserved.
#
# Save new baseline in database of public site and copy updated database to P&F site
#
# To run this script every midnight, edit the root crontab:
#   sudo crontab -e
#
# Enter this line:
#   0 0 * * * sh /opt/nav/bgt/baselines/save_baselines.sh > /opt/nav/bgt/baselines/save_baselines.log
#

# Set working directory to public site
cd /opt/nav/bgt/baselines

# Save new baseline
/home/ea/anaconda3/bin/python ./save_baselines.py -h localhost -p 8000

# Copy updated database to P&F site
cp ./baselines.sqlite /opt/nav/bgt_/baselines/baselines.sqlite
