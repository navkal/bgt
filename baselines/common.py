import os
import sqlite3
import time



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


def save_timestamp( cur, timestamp=None ):
    if timestamp == None:
        timestamp = int( time.time() * 1000 )
    print( '---' )
    print( 'Timestamp:', time.strftime( '%b %d %Y %H:%M:%S', time.localtime( timestamp / 1000 ) ) )
    print( '---' )
    cur.execute( 'INSERT INTO Timestamps ( timestamp ) VALUES(?)', ( timestamp, ) )
    timestamp_id = cur.lastrowid
    return timestamp_id
