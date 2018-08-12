# Copyright 2018 BACnet Gateway.  All rights reserved.

import argparse
import sqlite3
import os
import collections
import json
import common

# Get arguments
parser = argparse.ArgumentParser( description='Get baseline values from database', add_help=False )
parser.add_argument( '-f', dest='csv_filename' )
parser.add_argument( '-c', dest='column_name' )
parser.add_argument( '-t', dest='timestamp' )
args = parser.parse_args()

db = '../bgt_db/baselines.sqlite'

baseline = {}

if os.path.exists( db ):

    # Connect to the database
    conn = sqlite3.connect( db )
    cur = conn.cursor()

    # Determine which timestamp to retrieve
    if args.timestamp:
        # Specified timestamp
        which_timestamp = '( SELECT MIN( timestamp ) FROM ( SELECT timestamp from Timestamps WHERE timestamp>=' + str( int( args.timestamp ) / 1000 ) + ' ) )'
    else:
        # Default: Latest timestamp
        which_timestamp = '( SELECT MAX( timestamp ) FROM Timestamps )'

    # Retrieve the timestamp
    cur.execute( 'SELECT id, timestamp FROM Timestamps WHERE timestamp=' + which_timestamp )
    timestamp_row = cur.fetchone()

    if timestamp_row:
        timestamp_id = timestamp_row[0]
        timestamp = timestamp_row[1] * 1000

        # Map CSV filename to ID
        view_id = common.get_id( 'Views', 'csv_filename', args.csv_filename, cursor=cur )

        # Map column name to ID
        column_id = common.get_id( 'Columns', 'column_name', args.column_name, cursor=cur )

        # Retrieve values
        cur.execute('''
            SELECT
                row_label, value, units
            FROM Baselines
                LEFT JOIN Rows ON Baselines.row_id=Rows.id
                LEFT JOIN Units ON Baselines.units_id=Units.id
            WHERE ( view_id=? AND column_id=? AND timestamp_id=? )
        ''', ( view_id, column_id, timestamp_id )
        )
        value_rows = cur.fetchall()
        values = {}
        for value_row in value_rows:
            values[value_row[0]] = { 'value': value_row[1], 'units': value_row[2] }

        # Find earliest timestamp available for target graph
        if values:
            cur.execute( 'SELECT MIN( timestamp ) FROM Timestamps WHERE id in ( SELECT timestamp_id FROM Baselines WHERE ( view_id=? AND column_id=? ) )', ( view_id, column_id ) )
            first_timestamp = cur.fetchone()[0] * 1000

            cur.execute( 'SELECT MAX( timestamp ) FROM Timestamps WHERE id in ( SELECT timestamp_id FROM Baselines WHERE ( view_id=? AND column_id=? ) )', ( view_id, column_id ) )
            last_timestamp = cur.fetchone()[0] * 1000

            # Build baseline data structure consisting of values and timestamps
            values = collections.OrderedDict( sorted( values.items() ) )
            baseline = { 'timestamp': timestamp, 'first_timestamp': first_timestamp, 'last_timestamp': last_timestamp, 'values': values }
            baseline = collections.OrderedDict( sorted( baseline.items() ) )

# Return baseline
print( json.dumps( baseline ) )
