# Copyright 2018 BACnet Gateway.  All rights reserved.

import common
import csv
import pandas as pd

#
# Note: As currently implemented, this script requires bar labels to be unique,
# although they may appear in different views!
#

# Open the database
conn, cur = common.open_db( remove=True )

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
df_history = pd.read_csv( 'history.csv', na_filter=False, comment='#' )
print( '\nhistory columns\n', df_history.columns )
