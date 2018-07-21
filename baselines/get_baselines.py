# Copyright 2018 BACnet Gateway.  All rights reserved.

import argparse
import sqlite3
import os
import json

# Get arguments
parser = argparse.ArgumentParser( description='Get baseline values from database', add_help=False )
parser.add_argument( '-d', dest='delta_graphs' )
args = parser.parse_args()
delta_graphs = json.loads( args.delta_graphs )

# Initialize empty list of baseline values
baselines = []

# Built list from database
db = 'baselines/baselines.sqlite'

if os.path.exists( db ):
    conn = sqlite3.connect( db )
    cur = conn.cursor()

    for delta_graph in delta_graphs:

        cur.execute( 'SELECT * FROM Baselines WHERE ( csv_filename=? AND column_name=? )', ( delta_graph['csv_filename'], delta_graph['column_name'] ) )
        baselines.extend( cur.fetchall() )

# Return list of baseline values
print( json.dumps( baselines ) )





