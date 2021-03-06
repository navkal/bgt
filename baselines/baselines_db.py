# Copyright 2018 Building Energy Monitor.  All rights reserved.

import os
import sqlite3
import time
import datetime


import sys
sys.path.append( '../../bg/util' )
import db_util


cur = None
conn = None

nothing = ( None, '' )


def open_db( remove=False ):
    global cur
    global conn

    db = '../../bgt_db/baselines.sqlite'

    if ( remove ):
        try:
            os.remove( db )
        except:
            pass

    db_exists = os.path.exists( db )

    conn = sqlite3.connect( db )
    cur = conn.cursor()

    if not db_exists:

        cur.executescript('''

            CREATE TABLE IF NOT EXISTS Timestamps (
                id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
                timestamp INTEGER UNIQUE
            );

            CREATE TABLE IF NOT EXISTS Baselines (
                id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
                view_id INTEGER,
                column_id INTEGER,
                row_id INTEGER,
                value INTEGER,
                units_id INTEGER,
                timestamp_id INTEGER
            );

            CREATE TABLE IF NOT EXISTS Views (
                id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
                csv_filename TEXT UNIQUE
            );

            CREATE TABLE IF NOT EXISTS Columns (
                id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
                column_name TEXT UNIQUE
            );

            CREATE TABLE IF NOT EXISTS Rows (
                id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
                row_label TEXT UNIQUE
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

    # Save timestamp
    timestamp_id = db_util.save_field( 'Timestamps', 'timestamp', int( date.timestamp() ), cur )

    return timestamp_id, date.strftime('%m/%d/%Y')


def save_baseline_value( csv_filename, column_name, row_label, value, units, timestamp_id ):

    if not ( ( value in nothing ) or ( units in nothing ) ):

        view_id = db_util.save_field( 'Views', 'csv_filename', csv_filename, cur )
        column_id = db_util.save_field( 'Columns', 'column_name', column_name, cur )
        row_id = db_util.save_field( 'Rows', 'row_label', row_label, cur )
        units_id = db_util.save_field( 'Units', 'units', units, cur )

        # Determine whether an entry is already present for this view, column, row, and timestamp
        cur.execute( 'SELECT id FROM Baselines WHERE ( view_id=? AND column_id=? AND row_id=? AND timestamp_id=? )', ( view_id, column_id, row_id, timestamp_id ) )
        baseline_rows = cur.fetchall()

        if not baseline_rows:
            # Entry does not exist; create it
            cur.execute( 'INSERT INTO Baselines ( view_id, column_id, row_id, value, units_id, timestamp_id ) VALUES (?,?,?,?,?,?)', ( view_id, column_id, row_id, value, units_id, timestamp_id ) )

        return ( view_id, column_id, row_id )


def commit():
    conn.commit()
