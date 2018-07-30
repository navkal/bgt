import os
import sqlite3
import time



def open_db( remove=False ):

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


def save_timestamp( cur, timestamp=None ):

    if timestamp == None:
        timestamp = int( time.time() )

    timestamp *= 1000

    print( '---' )
    print( 'Timestamp:', time.strftime( '%b %d %Y %H:%M:%S', time.localtime( timestamp / 1000 ) ) )
    print( '---' )

    # Find out if this timestamp already exists
    cur.execute( 'SELECT id FROM Timestamps WHERE timestamp=?', ( timestamp, ) )
    rows = cur.fetchall()

    if rows:
        # Timestmap exists; get its id
        timestamp_id = rows[0][0]
    else:
        # Timestamp does not exist; insert it
        cur.execute( 'INSERT INTO Timestamps ( timestamp ) VALUES(?)', ( timestamp, ) )
        timestamp_id = cur.lastrowid

    return timestamp_id


def save_baseline_value( cur, csv_filename, column_name, row_label, value, units, timestamp_id ):

    if ( value and units ):
        cur.execute( 'SELECT id FROM Baselines WHERE ( csv_filename=? AND column_name=? AND row_label=? AND timestamp_id=? )', ( csv_filename, column_name, row_label, timestamp_id ) )
        rows = cur.fetchall()
        if rows:
            print( 'UPDATE' )
            cur.execute( 'UPDATE Baselines SET value=?, units=? WHERE id=?', ( value, units, timestamp_id ) )
        else:
            print( 'INSERT' )
            cur.execute( 'INSERT INTO Baselines ( csv_filename, column_name, row_label, value, units, timestamp_id ) VALUES (?,?,?,?,?,?)', ( csv_filename, column_name, row_label, value, units, timestamp_id ) )
