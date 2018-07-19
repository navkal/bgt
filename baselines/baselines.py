# Copyright 2018 BACnet Gateway.  All rights reserved.

import argparse
import sqlite3
import os
import csv
import pandas as pd
import time
from bacnet_gateway_requests import get_value_and_units




def open_db():

    db = 'baselines.sqlite'
    db_exists = os.path.exists( db )

    conn = sqlite3.connect( db )
    cur = conn.cursor()

    if not db_exists:

        cur.executescript('''
            CREATE TABLE IF NOT EXISTS Baselines (
                id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
                facility TEXT,
                oid TEXT,
                value FLOAT,
                units TEXT,
                timestamp FLOAT
            );
        ''');

        conn.commit()

    return conn, cur


def save_baselines( baselines_row ):

    # Read spreadsheet into a dataframe.
    # Each row contains the following:
    #   - Facility
    #   - Instance ID of power meter
    #   - Instance ID of energy meter
    filename = baselines_row[0]
    df = pd.read_csv( '../csv/' + filename + '.csv', na_filter=False, comment='#' )

    # Output column headings
    print( '---' )
    print( 'CSV file:', filename )
    print( '---' )
    oid_column_name = baselines_row[1]
    print( 'Timestamp,Facility,' + oid_column_name + ',' + oid_column_name + 'Units' )

    # Iterate over the rows of the dataframe, getting values for each row
    for index, oid_row in df.iterrows():
        save_baseline( oid_column_name, oid_row )


def save_baseline( oid_column_name, oid_row ):

    # Retrieve data
    facility = oid_row['Facility']
    oid = oid_row[oid_column_name]
    value, units = get_value_and_units( facility, oid, args.hostname, args.port )
    value = int( value ) if value else ''
    units = units if units else ''

    # Debug
    print( '{0},{1},{2},{3}'.format( time.ctime( timestamp ), oid_row['Label'], value, units ) )

    # Save in database
    cur.execute( 'SELECT id FROM Baselines WHERE ( facility=? AND oid=? )', ( facility, oid ) )
    row = cur.fetchone()
    if row:
        cur.execute( 'UPDATE Baselines SET value=?, units=?, timestamp=? WHERE id=?', ( value, units, timestamp, row[0] ) )
    else:
        cur.execute( 'INSERT INTO Baselines ( facility, oid, value, units, timestamp ) VALUES(?,?,?,?,?)', ( facility, oid, value, units, timestamp ) )

    conn.commit()


if __name__ == '__main__':

    # Get hostname and port of BACnet Gateway, and name of input CSV file
    parser = argparse.ArgumentParser( description='Test BACnet Gateway', add_help=False )
    parser.add_argument( '-h', dest='hostname' )
    parser.add_argument( '-p', dest='port' )
    args = parser.parse_args()

    # Open the database
    conn, cur = open_db()

    # Set timestamp for this run
    timestamp = time.time()

    # Update the baselines
    with open( 'baselines.csv', newline='' ) as csvfile:

        reader = csv.reader( csvfile )

        for baselines_row in reader:
            save_baselines( baselines_row )


