# Copyright 2018 Building Energy Monitor.  All rights reserved.

import sqlite3
import datetime
import time


# Connect to the database
conn = sqlite3.connect( '../../bgt_db/baselines.sqlite' )
cur = conn.cursor()

# Retrieve all timestamp IDs in descending order
cur.execute( 'SELECT id FROM Timestamps ORDER BY id DESC' )
rows = cur.fetchall()

# Iterate over descending IDs, replacing timestamps with consecutive days, starting with today
day = datetime.datetime.today()
for row in rows:
    timestamp = int( time.mktime( day.timetuple() ) )
    date = datetime.datetime.fromtimestamp( timestamp ).replace( hour=0, minute=0, second=0, microsecond=0 )
    timestamp = int( date.timestamp() )
    id = row[0]
    cur.execute( 'UPDATE Timestamps SET timestamp=? WHERE id=?', ( timestamp, id ) )
    print( id, date.strftime('%m/%d/%Y') )
    day -= datetime.timedelta( days=1 )

# Commit changes
conn.commit()
