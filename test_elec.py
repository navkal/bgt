# Copyright 2018 BACnet Gateway.  All rights reserved.

import pandas as pd
from get_value import get_present_value

# Read spreadsheet into a dataframe.
# Each row contains the following
#   - Feeder
#   - Instance ID of electric meter

df = pd.read_excel(
  'test_elec.xlsx',
  converters={ 'Meter':int }
)

# Replace nan values with zero
df = df.fillna( 0 )

print( 'Feeder,Meter,Units' )

# Iterate over the rows of the dataframe, getting meter readings for each feeder
for index, row in df.iterrows():
    print( '{0},{1}'.format( row['Feeder'], get_present_value( row['Meter'] ) ) )
