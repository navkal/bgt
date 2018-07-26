# Copyright 2018 BACnet Gateway.  All rights reserved.

import sqlite3

# Connect to the database
conn = sqlite3.connect( 'baselines.sqlite' )
cur = conn.cursor()


cur.execute( 'SELECT id FROM Timestamps ORDER BY id DESC' )
rows = cur.fetchall()

for row in rows:
    id = row[0]
    print( id )



exit()

# Determine which timestamp to retrieve
if args.timestamp:
    # Specified timestamp
    which_timestamp = '( SELECT MIN( timestamp ) FROM ( SELECT timestamp from Timestamps WHERE timestamp>=' + args.timestamp + ' ) )'
else:
    # Default: Latest timestamp
    which_timestamp = '( SELECT MAX( timestamp ) FROM Timestamps )'

# Retrieve the timestamp
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

        cur.execute( 'SELECT timestamp from Timestamps where id=( SELECT MAX( timestamp_id ) FROM Baselines WHERE ( csv_filename=? AND column_name=? ) )', ( args.csv_filename, args.column_name ) )
        last_timestamp = cur.fetchone()[0]



