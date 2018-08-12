# Copyright 2018 BACnet Gateway.  All rights reserved.

import argparse
import os
import sqlite3
import time
import datetime


cur = None
conn = None

def open_db():
    global cur
    global conn

    db = '../../bgt_db/cache.sqlite'

    if ( args.remove ):
        try:
            os.remove( db )
        except:
            pass

    db_exists = os.path.exists( db )

    conn = sqlite3.connect( db )
    cur = conn.cursor()

    if not db_exists:

        cur.executescript('''

            CREATE TABLE IF NOT EXISTS Cache (
                id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
                view_id INTEGER,
                facility_id INTEGER,
                instance INTEGER,
                value INTEGER,
                units_id INTEGER,
                timestamp INTEGER
            );

            CREATE TABLE IF NOT EXISTS Views (
                id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
                csv_filename TEXT UNIQUE
            );

            CREATE TABLE IF NOT EXISTS Facilities (
                id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
                facility_name TEXT UNIQUE
            );

            CREATE TABLE IF NOT EXISTS Units (
                id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
                units TEXT UNIQUE
            );

        ''')

        conn.commit()

    return conn, cur


def save_timestamp( timestamp=None ):

    if timestamp == None:
        timestamp = time.time()

    # Normalize timestamp
    date = datetime.datetime.fromtimestamp( timestamp ).replace( hour=0, minute=0, second=0, microsecond=0 )
    print( date.strftime('%m/%d/%Y') )

    # Save timestamp
    timestamp_id = save_field( 'Timestamps', 'timestamp', int( date.timestamp() ) )

    return timestamp_id


def save_field( table, field_name, field_value ):

    # Find out if this field value already exists in the specified table
    row_id = get_id( table, field_name, field_value )

    # Field value does not exist; insert it
    if row_id == None:
        cur.execute( 'INSERT INTO ' + table + ' ( ' + field_name + ' ) VALUES(?)', ( field_value, ) )
        row_id = cur.lastrowid

    # Return id
    return row_id


def get_id( table, field_name, field_value, cursor=None ):

    if not cursor:
        cursor = cur

    # Retrieve ID corresponding to supplied field value
    cursor.execute( 'SELECT id FROM ' + table + ' WHERE ' + field_name + '=?', ( field_value, ) )
    row = cursor.fetchone()

    if row:
        row_id = row[0]
    else:
        row_id = None

    # Return id
    return row_id


def save_baseline_value( csv_filename, column_name, row_label, value, units, timestamp_id ):

    if ( value and units ):

        view_id = save_field( 'Views', 'csv_filename', csv_filename )
        column_id = save_field( 'Columns', 'column_name', column_name )
        row_id = save_field( 'Rows', 'row_label', row_label )
        units_id = save_field( 'Units', 'units', units )

        # Determine whether an entry is already present for this view, column, row, and timestamp
        cur.execute( 'SELECT id FROM Baselines WHERE ( view_id=? AND column_id=? AND row_id=? AND timestamp_id=? )', ( view_id, column_id, row_id, timestamp_id ) )
        baseline_rows = cur.fetchall()

        if not baseline_rows:
            # Entry does not exist; create it
            cur.execute( 'INSERT INTO Baselines ( view_id, column_id, row_id, value, units_id, timestamp_id ) VALUES (?,?,?,?,?,?)', ( view_id, column_id, row_id, value, units_id, timestamp_id ) )


def commit():
    conn.commit()


if __name__ == '__main__':

    # Get hostname and port of BACnet Gateway
    parser = argparse.ArgumentParser( description='Save recent values in cache', add_help=False )
    parser.add_argument( '-h', dest='hostname' )
    parser.add_argument( '-p', dest='port' )
    parser.add_argument( '-r', dest='remove', action='store_true' )
    args = parser.parse_args()

    # Open the database
    conn, cur = open_db()

    # Traverse CSV files.  Each represents one view.
    for root, dirs, files in os.walk( '../csv/' ):
        for filename in files:
            print( filename )
    exit()














    # Save timestamp of this operation
    print( 'Saving new baselines on' )
    timestamp_id = save_timestamp()

    # Update the baselines
    with open( 'baselines.csv', newline='' ) as csvfile:

        reader = csv.reader( csvfile )

        for baselines_row in reader:
            save_baselines( baselines_row )

    # Report missing dates, from the first database entry until today
    report_missing_dates()

    # Commit changes
    commit()
