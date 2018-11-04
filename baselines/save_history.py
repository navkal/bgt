# Copyright 2018 Building Energy Monitor.  All rights reserved.

import argparse
import baselines_db
import csv
import pandas as pd
import datetime

# Get filename and units
parser = argparse.ArgumentParser( description='Save historic baseline values in database', add_help=False )
parser.add_argument( '-b', dest='baselines_filename' )
parser.add_argument( '-h', dest='history_filename' )
parser.add_argument( '-u', dest='units' )
parser.add_argument( '-r', dest='remove', action='store_true' )
args = parser.parse_args()

# Initialize map of bar labels
bar_map = {}

# Get list of views for which we need baselines
with open( args.baselines_filename, newline='' ) as baselines_file:
    baselines_reader = csv.reader( baselines_file )

    for baselines_row in baselines_reader:
        csv_filename = baselines_row[0] # Name of file that describes the view
        column_name = baselines_row[1]  # Name of column in view that corresponds to a delta graph

        # Read view description file into dataframe
        df_view = pd.read_csv( '../csv/' + csv_filename + '.csv', na_filter=False, comment='#' )
        for index, row in df_view.iterrows():
            bar_map[ row.Label ] = { 'csv_filename': csv_filename, 'column_name': column_name }


# Read history file into dataframe
df = pd.read_csv( args.history_filename, index_col=[0] )

# Replace n/a with zeros
df = df.fillna( 0 )

# Trim column headers
df = df.rename( columns=lambda x: x.strip() )

df.index = pd.to_datetime( df.index, infer_datetime_format=True )
df = df.sort_index()


# Open the database
baselines_db.open_db( remove=args.remove )

for index, row in df.iterrows():
    timestamp_id, timestamp_text = baselines_db.save_timestamp( datetime.datetime.timestamp( index ) )
    print( timestamp_text );
    sr = df.loc[index]
    sr = sr[sr > 0]
    for row_label, value in sr.iteritems():
        csv_filename = bar_map[row_label]['csv_filename']
        column_name = bar_map[row_label]['column_name']
        baselines_db.save_baseline_value( csv_filename, column_name, row_label, int( value ), args.units, timestamp_id )

# Commit changes
baselines_db.commit()
