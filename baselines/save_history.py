# Copyright 2018 BACnet Gateway.  All rights reserved.


import common

barMap = {}

# Open the database
conn, cur = common.open_db( remove=True )
