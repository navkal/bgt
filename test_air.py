# Copyright 2018 BACnet Gateway.  All rights reserved.

import pandas as pd
from bacnet_gateway_requests import get_value_and_units

# Read spreadsheet into a dataframe.
# Each row contains the following
#   - Location
#   - Instance ID of CO2 sensor
#   - Instance ID of temperature sensor

df = pd.read_excel(
  'test_air.xlsx',
  converters={ 'CO2':int, 'Temperature':int }
)

# Replace NaN values with zero
df = df.fillna( 0 )

# Output column headings
print( 'Location,Temperature,Temperature Units,CO2,CO2 Units' )

# Iterate over the rows of the dataframe, getting temperature and CO2 values for each location
for index, row in df.iterrows():
    # Retrieve data
    temp_value, temp_units = get_value_and_units( row['Temperature'], '192.168.1.186', '8000' )
    co2_value, co2_units = get_value_and_units( row['CO2'], '192.168.1.186', '8000' )

    # Prepare to print
    temp_value = int( temp_value ) if temp_value else ''
    temp_units = temp_units if temp_units else ''
    co2_value = int( co2_value ) if co2_value else ''
    co2_units = co2_units if co2_units else ''

    # Output CSV format
    print( '{0},{1},{2},{3},{4}'.format( row['Location'], temp_value, temp_units, co2_value, co2_units ) )
