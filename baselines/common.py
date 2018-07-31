import os
import sqlite3
import time


cur = None
conn = None

def open_db( remove=False ):
    global cur
    global conn

    db = 'baselines.sqlite'

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
                timestamp FLOAT UNIQUE
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
        timestamp = int( time.time() )

    print( 'Timestamp:', time.strftime( '%b %d %Y %H:%M:%S', time.localtime( timestamp ) ) )

    timestamp *= 1000

    timestamp_id = save_field( 'Timestamps', 'timestamp', timestamp )

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

        cur.execute( 'SELECT id FROM Baselines WHERE ( view_id=? AND column_id=? AND row_id=? AND timestamp_id=? )', ( view_id, column_id, row_id, timestamp_id ) )
        rows = cur.fetchall()
        if rows:
            cur.execute( 'UPDATE Baselines SET value=?, units_id=? WHERE id=?', ( value, units_id, timestamp_id ) )
        else:
            cur.execute( 'INSERT INTO Baselines ( view_id, column_id, row_id, value, units_id, timestamp_id ) VALUES (?,?,?,?,?,?)', ( view_id, column_id, row_id, value, units_id, timestamp_id ) )


def commit():
    conn.commit()
