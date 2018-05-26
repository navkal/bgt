# Copyright 2018 BACnet Gateway.  All rights reserved.

import pandas as pd
from get_value import get_value_and_units

# Read spreadsheet into a dataframe.
# Each row contains the following
#   - Location
#   - Instance ID of CO2 sensor
#   - Instance ID of temperature sensor

df = pd.read_excel(
  'test_air.xlsx',
  converters={ 'CO2':int, 'Temperature':int }
)

# Replace nan values with zero
df = df.fillna( 0 )

print( 'Location,Temperature,Temperature Units,CO2,CO2 Units' )

# Iterate over the rows of the dataframe, getting temperature and CO2 values for each location
for index, row in df.iterrows():
    print( '{0},{1},{2}'.format( row['Location'], get_value_and_units( row['Temperature'] ), get_value_and_units( row['CO2'] ) ) )
