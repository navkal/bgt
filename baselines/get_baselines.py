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

if os.path.exists( db ):
    # Retrieve baselines from database
    conn = sqlite3.connect( db )
    cur = conn.cursor()
    cur.execute( 'SELECT * FROM Baselines WHERE ( csv_filename=? AND column_name=? )', ( args.csv_filename, args.column_name ) )
    baselines = cur.fetchall()
else:
    # No database; set empty list
    baselines = []

# Return list of baseline values
print( json.dumps( baselines ) )
