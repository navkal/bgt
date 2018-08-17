# Copyright 2018 BACnet Gateway.  All rights reserved.

import os
import sqlite3
import time

import sys
sys.path.append( '../util' )
import db_util


conn = None
cur = None


def open_db( remove=False ):
    global conn
    global cur

    db = '../../bgt_db/cache.sqlite'

    # Set ownership to ensure that this operation can be executed from apache
    try:
        from pwd import getpwnam
        from pathlib import Path
        pw_entry = getpwnam( 'www-data' )
        os.chown( Path( db ).parent, pw_entry.pw_uid, pw_entry.pw_gid )
    except Exception as e:
        print( e )

    # Optionally remove database
    if ( remove ):
        try:
            os.remove( db )
        except:
            pass

    # Determine whether database exists
    db_exists = os.path.exists( db )

    # Connect to database
    conn = sqlite3.connect( db )
    cur = conn.cursor()

    if not db_exists:

        # Set ownership to ensure that this operation can be executed from apache
        try:
            os.chown( db, 'www-data', 'www-data' )
        except Exception as e:
            print( e )

        # Initialize database
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


def save_value_and_units( view, facility, instance, value, units ):

    units_id = db_util.save_field( 'Units', 'units', units, cur )
    timestamp = int( time.time() )

    # Test whether entry for current view, facility, and instance already exists
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

        # Entry exists; update it
        cache_id = cache_row[0]
        cur.execute( 'UPDATE Cache SET value=?, units_id=?, timestamp=? WHERE id=?', ( value, units_id, timestamp, cache_id ) )

    else:

        # Entry does not exist; insert it
        view_id = db_util.save_field( 'Views', 'view', view, cur )
        facility_id = db_util.save_field( 'Facilities', 'facility', facility, cur )
        cur.execute( 'INSERT INTO Cache ( view_id, facility_id, instance, value, units_id, timestamp ) VALUES (?,?,?,?,?,?)', ( view_id, facility_id, instance, value, units_id, timestamp ) )

    conn.commit()
