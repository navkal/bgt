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
            CREATE TABLE IF NOT EXISTS Timestamps (
                id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
                timestamp FLOAT
            );

            CREATE TABLE IF NOT EXISTS Baselines (
                id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
                csv_filename TEXT,
                column_name TEXT,
                row_label TEXT,
                value INTEGER,
                units TEXT,
                timestamp_id
            );
        ''')

        conn.commit()

    return conn, cur


def save_timestamp():
    timestamp = int( time.time() * 1000 )
    print( '---' )
    print( 'Timestamp:', time.strftime( '%b %d %Y %H:%M:%S', time.localtime( timestamp / 1000 ) ) )
    print( '---' )
    cur.execute( 'INSERT INTO Timestamps ( timestamp ) VALUES(?)', ( timestamp, ) )
    timestamp_id = cur.lastrowid
    return timestamp_id


def save_baselines( baselines_row ):

    # Read spreadsheet into a dataframe.
    # Each row contains the following:
    #   - Facility
    #   - Instance ID of power meter
    #   - Instance ID of energy meter
    csv_filename = baselines_row[0]
    df = pd.read_csv( '../csv/' + csv_filename + '.csv', na_filter=False, comment='#' )

    # Output column headings
    print( '---' )
    print( 'CSV file:', csv_filename )
    print( '---' )
    column_name = baselines_row[1]
    print( 'Label,' + column_name + ',' + column_name + ' Units' )

    # Iterate over the rows of the dataframe, getting values for each row
    for index, oid_row in df.iterrows():
        save_baseline( csv_filename, column_name, oid_row )


def save_baseline( csv_filename, column_name, oid_row ):

    # Retrieve data, retrying if necessary
    facility = oid_row['Facility']
    oid = oid_row[column_name]
    row_label = oid_row['Label']
    for i in range( 1, 6 ):
        value, units = get_value_and_units( facility, oid, args.hostname, args.port )
        print( '{0},{1},{2}'.format( row_label, value, units ) )
        if ( value and units ):
            break

    # Save retrieved data in database
    if ( value and units ):
        cur.execute( 'INSERT INTO Baselines ( csv_filename, column_name, row_label, value, units, timestamp_id ) VALUES(?,?,?,?,?,?)', ( csv_filename, column_name, row_label, value, units, timestamp_id ) )


if __name__ == '__main__':

    # Get hostname and port of BACnet Gateway, and name of input CSV file
    parser = argparse.ArgumentParser( description='Save baseline values in database', add_help=False )
    parser.add_argument( '-h', dest='hostname' )
    parser.add_argument( '-p', dest='port' )
    args = parser.parse_args()

    # Open the database
    conn, cur = open_db()

    # Save timestamp of this operation
    timestamp_id = save_timestamp()

    # Update the baselines
    with open( 'baselines.csv', newline='' ) as csvfile:

        reader = csv.reader( csvfile )

        for baselines_row in reader:
            save_baselines( baselines_row )

    conn.commit()