# Copyright 2018 BACnet Gateway.  All rights reserved.

import argparse
import os
import sqlite3
import pandas as pd
import time

import sys
sys.path.append( '../util' )
from bacnet_gateway_requests import get_value_and_units


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

        print( 'creating database' )

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
                view TEXT UNIQUE
            );

            CREATE TABLE IF NOT EXISTS Facilities (
                id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
                facility TEXT UNIQUE
            );

            CREATE TABLE IF NOT EXISTS Units (
                id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
                units TEXT UNIQUE
            );

        ''')

        conn.commit()

    return conn, cur


def update_cache():

    # Traverse CSV files.  Each represents one view.
    for root, dirs, files in os.walk( '../csv/' ):

        for csv_filename in files:

            print( '----------->' )
            print( csv_filename )


            # Traverse all rows in the view

            df = pd.read_csv( '../csv/' + csv_filename, na_filter=False, comment='#' )
            view = os.path.splitext( csv_filename )[0]

            for index, view_row in df.iterrows():

                print( '==' )
                print( view_row )
                print( '==' )

                facility = view_row.iloc[1]

                # Traverse instances in current row
                for i in range( 2, len( view_row ) ):

                    instance = view_row.iloc[i]

                    if instance:

                        value, units = get_value_and_units( facility, instance, args.hostname, args.port )
                        print( facility, instance, value, units )

                        if value and units:

                            units_id = save_field( 'Units', 'units', units )
                            timestamp = int( time.time() )

                            cur.execute( '''
                                SELECT
                                    Cache.id
                                FROM Cache
                                    LEFT JOIN Views ON Cache.view_id=Views.id
                                    LEFT JOIN Facilities ON Cache.facility_id=Facilities.id
                                WHERE ( Views.view=? AND Facilities.facility=? AND instance=? );
                            ''', ( view, facility, instance )
                            )
                            cache_row = cur.fetchone()


                            if cache_row:

                                print( 'already there' )
                                cache_id = cache_row[0]
                                cur.execute( 'UPDATE Cache SET value=?, units_id=?, timestamp=? WHERE id=?', ( value, units_id, timestamp, cache_id ) )

                            else:

                                print( 'NOT there' )
                                view_id = save_field( 'Views', 'view', view )
                                facility_id = save_field( 'Facilities', 'facility', facility )
                                cur.execute( 'INSERT INTO Cache ( view_id, facility_id, instance, value, units_id, timestamp ) VALUES (?,?,?,?,?,?)', ( view_id, facility_id, instance, value, units_id, timestamp ) )

                            conn.commit()






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
    
    ps = os.popen( 'ps -elf' ).read()
    dups = ps.count( __file__ )
    
    if dups > 1:
        print( 'Duplicate process ' + __file__ + ' exiting' )

    else:

        # Get command line arguments
        parser = argparse.ArgumentParser( description='Save recent values in cache', add_help=False )
        parser.add_argument( '-h', dest='hostname' )
        parser.add_argument( '-p', dest='port' )
        parser.add_argument( '-r', dest='remove', action='store_true' )
        args = parser.parse_args()

        # Open the database
        conn, cur = open_db()

        # Update cache continuously
        while True:
            update_cache()

