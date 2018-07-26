# Copyright 2018 BACnet Gateway.  All rights reserved.

import argparse
import sqlite3
import os
import collections
import json

# Get arguments
parser = argparse.ArgumentParser( description='Get baseline values from database', add_help=False )
parser.add_argument( '-f', dest='csv_filename' )
parser.add_argument( '-c', dest='column_name' )
parser.add_argument( '-t', dest='timestamp' )
args = parser.parse_args()

db = 'baselines/baselines.sqlite'

baseline = {}

if os.path.exists( db ):

    # Connect to the database
    conn = sqlite3.connect( db )
    cur = conn.cursor()

    # Determine which timestamp to retrieve
    if args.timestamp:
        # Specified timestamp
        which_timestamp = '( SELECT MIN( timestamp ) FROM ( SELECT timestamp from Timestamps WHERE timestamp>=' + args.timestamp + ' ) )'
    else:
        # Default: Latest timestamp
        which_timestamp = '( SELECT MAX( timestamp ) FROM Timestamps )'

    # Retrieve the timestamp
    cur.execute( 'SELECT id, timestamp FROM Timestamps WHERE timestamp=' + which_timestamp )
    timestamp_row = cur.fetchone()

    if timestamp_row:
        timestamp_id = timestamp_row[0]
        timestamp = timestamp_row[1]

        # Retrieve values
        cur.execute( 'SELECT row_label, value, units FROM Baselines WHERE ( csv_filename=? AND column_name=? AND timestamp_id=? )', ( args.csv_filename, args.column_name, timestamp_id ) )
        value_rows = cur.fetchall()
        values = {}
        for value_row in value_rows:
            values[value_row[0]] = { 'value': value_row[1], 'units': value_row[2] }


        # Find earliest timestamp available for target graph
        if values:
            cur.execute( 'SELECT timestamp from Timestamps where id=( SELECT MIN( timestamp_id ) FROM Baselines WHERE ( csv_filename=? AND column_name=? ) )', ( args.csv_filename, args.column_name ) )
            first_timestamp = cur.fetchone()[0]

            # Build baseline data structure consisting of values and timestamps
            values = collections.OrderedDict( sorted( values.items() ) )
            baseline = { 'timestamp': timestamp, 'first_timestamp': first_timestamp, 'values': values }
            baseline = collections.OrderedDict( sorted( baseline.items() ) )

# Return baseline
print( json.dumps( baseline ) )
