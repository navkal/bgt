# Copyright 2018 BACnet Gateway.  All rights reserved.


import common
import csv

barMap = {}

# Open the database
conn, cur = common.open_db( remove=True )


# Get list of views for which we need baselines
with open( 'baselines.csv', newline='' ) as csvfile:

    reader = csv.reader( csvfile )

    for baselines_row in reader:
        print( baselines_row )
