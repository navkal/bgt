# Copyright 2018 BACnet Gateway.  All rights reserved.

import argparse
import json
import pandas as pd


view_values = {}


# Get arguments
parser = argparse.ArgumentParser( description='Request all values of specified view from BACnet Gateway cache' )
parser.add_argument( '-v', dest='view' )
args = parser.parse_args()

if args.view:

    # Load dataframe representing requested view
    df = pd.read_csv( 'csv/' + args.view + '.csv', na_filter=False, comment='#' )

    # Build request
    bulk_request = []

    for index, row in df.iterrows():

        # Extract facility from row
        facility = row.iloc[1]

        # Traverse instances in current row
        for i in range( 2, len( row ) ):

            instance = row.iloc[i]

            if instance:

                # Add facility/instance pair to request
                pair = { 'facility': facility, 'instance': instance }
                bulk_request.append( pair )
                print( len( bulk_request ), pair )





# Return view
print( json.dumps( view_values ) )
