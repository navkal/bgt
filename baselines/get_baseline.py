# Copyright 2018 BACnet Gateway.  All rights reserved.

import argparse
import sqlite3
import os
import json

# Get arguments
parser = argparse.ArgumentParser( description='Get baseline values from database', add_help=False )
parser.add_argument( '-f', dest='csv_filename' )
parser.add_argument( '-c', dest='column_name' )
args = parser.parse_args()

db = 'baselines/baselines.sqlite'

values = {}

if os.path.exists( db ):
    # Retrieve baseline values from database
    conn = sqlite3.connect( db )
    cur = conn.cursor()

    cur.execute( 'SELECT timestamp FROM Timestamps' )
    timestamp = cur.fetchone()[0] * 1000

    cur.execute( 'SELECT * FROM Baselines WHERE ( csv_filename=? AND column_name=? )', ( args.csv_filename, args.column_name ) )
    rows = cur.fetchall()
    for row in rows:
        values[row[3]] = { 'value': row[4], 'units': row[5] }

# Return timestamp and values
baseline = { 'timestamp': timestamp, 'values': values }
print( json.dumps( baseline ) )
