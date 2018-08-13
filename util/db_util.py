# Copyright 2018 BACnet Gateway.  All rights reserved.

import sqlite3

def save_field( table, field_name, field_value, cursor ):

    # Find out if this field value already exists in the specified table
    row_id = get_id( table, field_name, field_value, cursor )

    # Field value does not exist; insert it
    if row_id == None:
        cursor.execute( 'INSERT INTO ' + table + ' ( ' + field_name + ' ) VALUES(?)', ( field_value, ) )
        row_id = cursor.lastrowid

    # Return id
    return row_id


def get_id( table, field_name, field_value, cursor ):

    # Retrieve ID corresponding to supplied field value
    cursor.execute( 'SELECT id FROM ' + table + ' WHERE ' + field_name + '=?', ( field_value, ) )
    row = cursor.fetchone()

    if row:
        row_id = row[0]
    else:
        row_id = None

    # Return id
    return row_id
