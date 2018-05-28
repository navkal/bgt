# Copyright 2018 BACnet Gateway.  All rights reserved.

import argparse
import pandas as pd
from bacnet_gateway_requests import get_value_and_units

# Get hostname and port of BACnet Gateway
parser = argparse.ArgumentParser( description='Test BACnet Gateway', add_help=False )
parser.add_argument( '-h', dest='hostname' )
parser.add_argument( '-p', dest='port' )
args = parser.parse_args()

# Read spreadsheet into a dataframe.
# Each row contains the following:
#   - Location
#   - Instance ID of CO2 sensor
#   - Instance ID of temperature sensor
df = pd.read_csv( 'test_air.csv', na_filter=False )

# Output column headings
print( 'Location,Temperature,Temperature Units,CO2,CO2 Units' )

# Iterate over the rows of the dataframe, getting temperature and CO2 values for each location
for index, row in df.iterrows():

    # Retrieve data
    temp_value, temp_units = get_value_and_units( row['Temperature'], args.hostname, args.port )
    co2_value, co2_units = get_value_and_units( row['CO2'], args.hostname, args.port )

    # Prepare to print
    temp_value = int( temp_value ) if temp_value else ''
    temp_units = temp_units if temp_units else ''
    co2_value = int( co2_value ) if co2_value else ''
    co2_units = co2_units if co2_units else ''

    # Output CSV format
    print( '{0},{1},{2},{3},{4}'.format( row['Location'], temp_value, temp_units, co2_value, co2_units ) )
