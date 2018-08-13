# Copyright 2018 BACnet Gateway.  All rights reserved.

import argparse
import os
import sqlite3
import pandas as pd
import time

import sys
sys.path.append( '../util' )
from bacnet_gateway_requests import get_value_and_units
import db_util


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

                            units_id = db_util.save_field( 'Units', 'units', units, cur )
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
                                view_id = db_util.save_field( 'Views', 'view', view )
                                facility_id = db_util.save_field( 'Facilities', 'facility', facility )
                                cur.execute( 'INSERT INTO Cache ( view_id, facility_id, instance, value, units_id, timestamp ) VALUES (?,?,?,?,?,?)', ( view_id, facility_id, instance, value, units_id, timestamp ) )

                            conn.commit()





if __name__ == '__main__':

    # Get list of running processes
    ps = os.popen( 'ps -elf' ).read()

    # Find out how many occurrences of this script are running
    dups = ps.count( __file__ )

    # If multiple occurrences of this script are running, exit
    if dups > 1:

        # Do nothing
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

