# Copyright 2018 BACnet Gateway.  All rights reserved.

import argparse
import json
import pandas as pd

import sys
sys.path.append( 'util' )
from bacnet_gateway_requests import get_bulk


# Initialize empty result
bulk_rsp = []

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

    # Issue get-bulk request
    bulk_rsp = get_bulk( bulk_request, args.hostname, args.port )
    
    # Build map from bulk response   
    view_rsp = {}
    for rsp in bulk_rsp:
        rsp['success'] = True
        facility = rsp['facility']
        if facility not in view_rsp:
            view_rsp[facility] = {}
        view_rsp[facility][rsp['instance']] = rsp

# Return view
print( json.dumps( view_rsp ) )
