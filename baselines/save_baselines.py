# Copyright 2018 BACnet Gateway.  All rights reserved.

import argparse
import csv
import pandas as pd
from bacnet_gateway_requests import get_value_and_units
import common

nothing = ( None, '' )

def save_baselines( baselines_row ):

    # Read spreadsheet into a dataframe.
    # Each row contains the following:
    #   - Facility
    #   - Instance ID of power meter
    #   - Instance ID of energy meter
    csv_filename = baselines_row[0]
    df = pd.read_csv( '../csv/' + csv_filename + '.csv', na_filter=False, comment='#' )

    # Output column headings
    print( '---' )
    print( 'CSV file:', csv_filename )
    print( '---' )
    column_name = baselines_row[1]
    print( 'Label,' + column_name + ',' + column_name + ' Units' )

    # Iterate over the rows of the dataframe, getting values for each row
    for index, oid_row in df.iterrows():
        save_baseline( csv_filename, column_name, oid_row )


def save_baseline( csv_filename, column_name, oid_row ):

    # Retrieve data, retrying if necessary
    facility = oid_row['Facility']
    oid = oid_row[column_name]
    row_label = oid_row['Label']
    for i in range( 1, 6 ):
        value, units = get_value_and_units( facility, oid, args.hostname, args.port )
        print( '{0},{1},{2}'.format( row_label, value, units ) )
        if ( not ( ( value in nothing ) or ( units in nothing ) ) ):
            common.save_baseline_value( csv_filename, column_name, row_label, value, units, timestamp_id )
            break


def report_missing_dates():

    # Retrieve distinct timestamps from database
    cur.execute('''
        SELECT DISTINCT
            timestamp
        FROM Baselines
            LEFT JOIN Timestamps ON Baselines.timestamp_id=Timestamps.id
    ''')
    timestamp_rows = cur.fetchall()

    # Debug
    del timestamp_rows[123]
    del timestamp_rows[123]
    del timestamp_rows[234]

    # Construct and sort dataframe of timestamps
    df = pd.DataFrame( timestamp_rows, columns=['timestamp'] )
    df = df.sort_values( by=['timestamp'] )

    # Extract date and find row-to-row differences
    df['datetime'] = pd.to_datetime( df['timestamp'], unit='ms' )
    df['date'] = pd.DatetimeIndex( df['datetime'] ).normalize()
    df['diff'] = df['date'].diff()

    # Remove first row, where difference is irrelevant
    df = df.iloc[1:]

    # Look for omissions
    df = df[ df['diff'].ne( '1 days' ) ]

    # Report omissions, if any
    if len( df ):
        print( '\n!!! Omissions found in database !!!\n' )
        df = df[ [ 'date', 'diff' ] ]
        df = df.rename( index=str, columns={ 'date': 'Ending at', 'diff': 'Interval between readings' } )
        print( df.to_string( index=False ) )

    return


if __name__ == '__main__':

    # Get hostname and port of BACnet Gateway, and name of input CSV file
    parser = argparse.ArgumentParser( description='Save baseline values in database', add_help=False )
    parser.add_argument( '-h', dest='hostname' )
    parser.add_argument( '-p', dest='port' )
    parser.add_argument( '-r', dest='remove', action='store_true' )
    args = parser.parse_args()

    # Open the database
    conn, cur = common.open_db( remove=args.remove )

    # Save timestamp of this operation
    timestamp_id = common.save_timestamp()

    # Update the baselines
    with open( 'baselines.csv', newline='' ) as csvfile:

        reader = csv.reader( csvfile )

        for baselines_row in reader:
            save_baselines( baselines_row )

    # Report missing dates, from the first database entry until today
    report_missing_dates()

    # Commit changes
    common.commit()
