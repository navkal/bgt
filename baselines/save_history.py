# Copyright 2018 BACnet Gateway.  All rights reserved.

import argparse
import common
import csv
import pandas as pd
import datetime

# Get filename and units
parser = argparse.ArgumentParser( description='Save historic baseline values in database', add_help=False )
parser.add_argument( '-h', dest='history_filename' )
parser.add_argument( '-u', dest='units' )
args = parser.parse_args()

# Initialize map of bar labels
bar_map = {}

# Get list of views for which we need baselines
with open( 'baselines.csv', newline='' ) as baselines_file:
    baselines_reader = csv.reader( baselines_file )

    for baselines_row in baselines_reader:
        csv_filename = baselines_row[0] # Name of file that describes the view
        column_name = baselines_row[1]  # Name of column in view that corresponds to a delta graph

        # Read view description file into dataframe
        df_view = pd.read_csv( '../csv/' + csv_filename + '.csv', na_filter=False, comment='#' )
        for index, row in df_view.iterrows():
            bar_map[ row.Label ] = { 'csv_filename': csv_filename, 'column_name': column_name }

print( '\nbar map\n', bar_map )


# Read history file into dataframe
df = pd.read_csv( args.history_filename, index_col=[0] )

# Replace n/a with zeros
df = df.fillna( 0 )

# Trim column headers
df = df.rename( columns=lambda x: x.strip() )

df.index = pd.to_datetime( df.index, infer_datetime_format=True )
df = df.sort_index()


# Open the database
conn, cur = common.open_db( remove=False )

for index, row in df.iterrows():
    print( '=============' )
    timestamp_id = common.save_timestamp( cur, datetime.datetime.timestamp( index ) )
    print( 'timestamp_id', timestamp_id )
    sr = df.loc[index]
    sr = sr[sr > 0]
    for row_label, value in sr.iteritems():
        csv_filename = bar_map[row_label]['csv_filename']
        column_name = bar_map[row_label]['column_name']
        common.save_baseline_value( cur, csv_filename, column_name, row_label, value, args.units, timestamp_id )

conn.commit()
