# Copyright 2018 BACnet Gateway.  All rights reserved.

import os
import argparse
import sqlite3
import collections
import json


cached_values = {}

db = '../bgt_db/cache.sqlite'

if os.path.exists( db ):

    # Get arguments
    parser = argparse.ArgumentParser( description='Get cached values for specified view' )
    parser.add_argument( '-v', dest='view' )
    args = parser.parse_args()

    if args.view:

        # Connect to the database
        conn = sqlite3.connect( db )
        cur = conn.cursor()

        cur.execute( '''
            SELECT facility, instance, value, units, timestamp
            FROM Cache
                LEFT JOIN Views ON Cache.view_id=Views.id
                LEFT JOIN Facilities ON Cache.facility_id=Facilities.id
                LEFT JOIN Units ON Cache.units_id=Units.id
            WHERE Views.view=?
        ''', ( args.view, )
        )

        rows = cur.fetchall()

        for row in rows:
            facility = row[0]
            instance = row[1]
            cached_value = { 'facility': facility, 'instance': instance, 'value': row[2], 'units': row[3], 'timestamp': row[4] }
            if facility not in cached_values:
                cached_values[facility] = {}
            cached_values[facility][instance] = cached_value

        cached_values = collections.OrderedDict( sorted( cached_values.items() ) )

# Return view
print( json.dumps( cached_values ) )
