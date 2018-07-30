# Copyright 2018 BACnet Gateway.  All rights reserved.


import common
import csv

barMap = {}

# Open the database
conn, cur = common.open_db( remove=True )


# Get list of views for which we need baselines
with open( 'baselines.csv', newline='' ) as baselines_file:

    baselines_reader = csv.reader( baselines_file )

    for baselines_row in baselines_reader:
        print( baselines_row )
        view_filename = '../csv/' + baselines_row[0] + '.csv'
        print( view_filename )

        with open( view_filename, newline='' ) as view_file:

            view_reader = csv.reader( view_file )

            for view_row in view_reader:
                print( view_row )




