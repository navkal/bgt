# Copyright 2018 BACnet Gateway.  All rights reserved.

import pandas as pd
import requests
import json


# Get BACnet property
def get_property( property, instance ):

    # Set up request arguments
    args = {
        'address': '10.12.0.250',
        'type': 'analogInput',
        'instance': instance,
        'property': property
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
            result = property + ':' + ' ' + str( dc_data[property] ) + ' ' + dc_data['units']
        else:
            result = dc_data['message']

    else:
        result = dc_bn_rsp['message']

    return result


# Read spreadsheet into a dataframe.
# Each row contains the following
#   - Location
#   - Instance ID of CO2 sensor
#   - Instance ID of temperature sensor

df = pd.read_excel(
  'test.xlsx',
  converters={ 'CO2':int, 'Temperature':int }
)

# Replace nan values with zero
df = df.fillna( 0 )

# Iterate over the rows of the dataframe, getting CO2 and temperature values for each location
for index, row in df.iterrows():
    print( '\nLocation:', row['Location'] )
    print( '  CO2 -', get_property( 'presentValue', row['CO2'] ) )
    print( '  Temperature -', get_property( 'presentValue', row['Temperature'] ) )
