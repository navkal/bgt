# Copyright 2018 BACnet Gateway.  All rights reserved.

import sqlite3
import os
import json

baselines = []

db = 'baselines/baselines.sqlite'

if os.path.exists( db ):
    conn = sqlite3.connect( db )
    cur = conn.cursor()
    cur.execute( 'SELECT * FROM Baselines' )
    baselines = cur.fetchall()

print( json.dumps( baselines ) )
