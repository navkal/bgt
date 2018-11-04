# Copyright 2018 Building Energy Monitor.  All rights reserved.

import argparse
import csv
import pandas as pd
import time

import sys
sys.path.append( '../util' )
from building_data_requests import get_value
sys.path.append( '../../bg/util' )
import db_util

import baselines_db


def save_baselines( baselines_row ):

    # Read spreadsheet into a dataframe.
    # Each row contains the following:
    #   - Facility
    #   - Instance ID of power meter
    #   - Instance ID of energy meter
    csv_filename = baselines_row[0]
    df = pd.read_csv( '../csv/' + csv_filename + '.csv', na_filter=False, comment='#' )

    # Output column headings
    db_util.log( logpath, '---' )
    db_util.log( logpath, 'CSV file: ' + csv_filename )
    db_util.log( logpath, '---' )
    column_name = baselines_row[1]
    db_util.log( logpath, 'Label,' + column_name + ',' + column_name + ' Units' )

    # Iterate over the rows of the dataframe, getting values for each row
    for index, oid_row in df.iterrows():
        save_baseline( csv_filename, column_name, oid_row )


def save_baseline( csv_filename, column_name, oid_row ):

    # Retrieve data, retrying if necessary
    facility = oid_row['Facility']
    oid = oid_row[column_name]
    row_label = oid_row['Label']
    for i in range( 1, 6 ):
        value, units = get_value( facility, oid, args.hostname, args.port, live=True )
        value = int( value )
        db_util.log( logpath, '{0},{1},{2}'.format( row_label, value, units ) )
        baselines_db.save_baseline_value( csv_filename, column_name, row_label, value, units, timestamp_id )
        break


def report_missing_dates():

    # Retrieve sorted, distinct timestamps from database
    cur.execute('''
        SELECT DISTINCT
            timestamp
        FROM Baselines
            LEFT JOIN Timestamps ON Baselines.timestamp_id=Timestamps.id
        ORDER BY timestamp ASC;
    ''')
    timestamp_rows = cur.fetchall()

    # Load timestamps into dataframe
    df = pd.DataFrame( timestamp_rows, columns=['timestamp'] )

    # Extract dates
    df = df.multiply( 1000, ['timestamp'] )
    df['datetime'] = pd.to_datetime( df['timestamp'], unit='ms' )
    df['date'] = pd.DatetimeIndex( df['datetime'] ).normalize()

    # Calculate statistics
    stats = '\n'
    stats += '\nDates found in database'
    stats += '\nFirst: ' + str( df['date'].ix[0].date() )
    stats += '\nLast: ' + str( df['date'].ix[len(df)-1].date() )
    stats += '\nTotal: ' + str( len( df ) )
    stats += '\n'

    # Look for gaps
    df['diff'] = df['date'].diff()
    df = df.iloc[1:]
    df = df[ df['diff'].ne( '1 days' ) ]

    if len( df ):
        stats += '\nGaps found in database!'
        df = df[ [ 'date', 'diff' ] ]
        df = df.rename( index=str, columns={ 'date': 'Before', 'diff': 'Gap'  } )
        stats += '\n' + df.to_string( index=False )
    else:
        stats += '\nNo gaps found.'

    # Report statistics
    db_util.log( logpath, stats )

    return


if __name__ == '__main__':

    # Get hostname and port of BACnet Gateway
    parser = argparse.ArgumentParser( description='Save baseline values in database', add_help=False )
    parser.add_argument( '-h', dest='hostname' )
    parser.add_argument( '-p', dest='port' )
    parser.add_argument( '-r', dest='remove', action='store_true' )
    args = parser.parse_args()

    # Format log filename
    logpath = '../../bgt_db/save_baselines_' + time.strftime( '%Y-%m-%d_%H-%M-%S', time.localtime() ) + '.log'

    try:

        # Open the database
        conn, cur = baselines_db.open_db( remove=args.remove )

        # Save timestamp of this operation
        timestamp_id, timestamp_text = baselines_db.save_timestamp()
        db_util.log( logpath, 'Saving new baselines on ' + timestamp_text )

        # Update the baselines
        with open( 'baselines.csv', newline='' ) as csvfile:

            reader = csv.reader( csvfile )

            for baselines_row in reader:
                save_baselines( baselines_row )

        # Commit changes
        baselines_db.commit()

        # Report missing dates, from the first database entry until today
        report_missing_dates()

    except:
        db_util.log( logpath, '\nException: ' + sys.exc_info()[0] )
