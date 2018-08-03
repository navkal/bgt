# Copyright 2018 BACnet Gateway.  All rights reserved.

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
    timestamp = int( time.mktime( day.timetuple() ) ) * 1000
    id = row[0]
    cur.execute( 'UPDATE Timestamps SET timestamp=? WHERE id=?', ( timestamp, id ) )
    print( id, datetime.datetime.fromtimestamp( timestamp/1000 ) )
    day -= datetime.timedelta( days=1 )

# Commit changes
conn.commit()
