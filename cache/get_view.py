# Copyright 2018 Building Energy Monitor.  All rights reserved.

import argparse
import json
import pandas as pd

import sys
sys.path.append( 'util' )
from building_data_requests import get_bulk


# Initialize empty result
rsp_map = {}

# Get arguments
parser = argparse.ArgumentParser( description='Request all values of specified view from Building Energy Gateway cache', add_help=False )
parser.add_argument( '-v', dest='view' )
parser.add_argument( '-h', dest='hostname' )
parser.add_argument( '-p', dest='port' )
args = parser.parse_args()


if args.view and args.hostname and args.port:

    # Load dataframe representing requested view
    df = pd.read_csv( 'csv/' + args.view + '.csv', na_filter=False, comment='#' )

    # Initialize empty request
    bulk_request = []

    # Build request
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

    # Issue get-bulk request
    bulk_rsp = get_bulk( bulk_request, args.hostname, args.port )
    rsp_map = bulk_rsp['rsp_map']


# Return view
print( json.dumps( rsp_map ) )
