# Copyright 2018 BACnet Gateway.  All rights reserved.

import argparse
import json
import pandas as pd

import sys
sys.path.append( 'util' )
from bacnet_gateway_requests import get_bulk


view_values = {}


# Get arguments
parser = argparse.ArgumentParser( description='Request all values of specified view from BACnet Gateway cache', add_help=False )
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
                print( len( bulk_request ), pair )

    # If request is not empty, pass it to the gateway
    if len( bulk_request ):

        bulk_response = get_bulk( bulk_request, args.hostname, args.port )

        print( 'get_view got the following response from get_bulk' )
        print( bulk_response )

        view_values = bulk_response




# Return view
print( json.dumps( view_values ) )
