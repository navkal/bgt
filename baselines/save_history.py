# Copyright 2018 BACnet Gateway.  All rights reserved.


import common
import csv

bar_map = {}

# Open the database
conn, cur = common.open_db( remove=True )


# Get list of views for which we need baselines
with open( 'baselines.csv', newline='' ) as baselines_file:
    baselines_reader = csv.reader( baselines_file )

    for baselines_row in baselines_reader:
        csv_filename = baselines_row[0]
        column_name = baselines_row[1]

        with open( '../csv/' + csv_filename + '.csv', newline='' ) as view_file:
            view_reader = csv.reader( view_file )
            next( view_reader )

            for view_row in view_reader:
                bar_label = view_row[0]
                print( bar_label )
                bar_map[ bar_label ] = { 'csv_filename': csv_filename, 'column_name': column_name }

    print( bar_map )
