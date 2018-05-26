# Copyright 2018 BACnet Gateway.  All rights reserved.

import pandas as pd
import requests
import json


# Get BACnet property
def get_present_value( instance ):

    if instance:
        # Caller supplied non-empty instance

        # Set up request arguments
        args = {
            'facility': 'ahs',
            'instance': instance
        }

        # Issue request to HTTP service
        #host = '192.168.1.186'
        #host = '192.168.1.169'
        host = 'localhost'
        url = 'http://' + host + ':8000/bg.php'
        gateway_rsp = requests.post( url, data=args )

        # Convert JSON response to Python dictionary
        dc_rsp = json.loads( gateway_rsp.text )

        # Extract BACnet response from the dictionary
        dc_bn_rsp = dc_rsp['bacnet_response']

        # Extract result from BACnet response
        if ( dc_bn_rsp['success'] ):

            dc_data = dc_bn_rsp['data']

            if dc_data['success']:
                result = str( int( dc_data['presentValue'] ) ) + ',' + dc_data['units']
            else:
                result = dc_data['message'] + ','

        else:
            result = dc_bn_rsp['message'] + ','

    else:
        # Caller supplied empty instance
        result = ','

    return result


# Read spreadsheet into a dataframe.
# Each row contains the following
#   - Location
#   - Instance ID of electric meter

df = pd.read_excel(
  'test_elec.xlsx',
  converters={ 'Meter':int }
)

# Replace nan values with zero
df = df.fillna( 0 )

print( 'Feeder,Meter,Units' )

# Iterate over the rows of the dataframe, getting meter readings for specified feeders
for index, row in df.iterrows():
    print( '{0},{1}'.format( row['Feeder'], get_present_value( row['Meter'] ) ) )
