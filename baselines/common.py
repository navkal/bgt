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


def save_timestamp( timestamp=None ):

    if timestamp == None:
        timestamp = int( time.time() )

    print( 'Timestamp:', time.strftime( '%b %d %Y %H:%M:%S', time.localtime( timestamp ) ) )

    timestamp *= 1000

    timestamp_id = save_field( 'Timestamps', 'timestamp', timestamp )

    return timestamp_id


def save_field( table, field_name, field_value ):

    # Find out if this field value already exists in the specified table
    cur.execute( 'SELECT id FROM ' + table + ' WHERE ' + field_name + '=?', ( field_value, ) )
    rows = cur.fetchall()

    if rows:
        # Field value exists; get its id
        row_id = rows[0][0]
    else:
        # Field value does not exist; insert it
        cur.execute( 'INSERT INTO ' + table + ' ( ' + field_name + ' ) VALUES(?)', ( field_value, ) )
        row_id = cur.lastrowid

    # Return id
    return row_id


def save_baseline_value( csv_filename, column_name, row_label, value, units, timestamp_id ):

    if ( value and units ):
        cur.execute( 'SELECT id FROM Baselines WHERE ( csv_filename=? AND column_name=? AND row_label=? AND timestamp_id=? )', ( csv_filename, column_name, row_label, timestamp_id ) )
        rows = cur.fetchall()
        if rows:
            cur.execute( 'UPDATE Baselines SET value=?, units=? WHERE id=?', ( value, units, timestamp_id ) )
        else:
            cur.execute( 'INSERT INTO Baselines ( csv_filename, column_name, row_label, value, units, timestamp_id ) VALUES (?,?,?,?,?,?)', ( csv_filename, column_name, row_label, value, units, timestamp_id ) )


def commit():
    conn.commit()
