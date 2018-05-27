# Copyright 2018 BACnet Gateway.  All rights reserved.

import pandas as pd
from bacnet_gateway_requests import get_value_and_units

# Read spreadsheet into a dataframe.
# Each row contains the following
#   - Feeder
#   - Instance ID of electric meter

df = pd.read_excel(
  'test_elec.xlsx',
  converters={ 'Meter':int }
)

# Replace NaN values with zero
df = df.fillna( 0 )

# Output column headings
print( 'Feeder,Meter,Units' )

# Iterate over the rows of the dataframe, getting meter readings for each feeder
for index, row in df.iterrows():
    # Retrieve data
    value, units = get_value_and_units( row['Meter'], '192.168.1.186', '8000' )

    # Prepare to print
    value = int( value ) if value else ''
    units = units if units else ''

    # Output CSV format
    print( '{0},{1},{2}'.format( row['Feeder'], value, units ) )
