# Copyright 2018 BACnet Gateway.  All rights reserved.

import argparse
import cache_db

# Get command line arguments
parser = argparse.ArgumentParser( description='Maintain cache of recent values used by Building Monitor', add_help=False )
parser.add_argument( '-w', dest='view' )
parser.add_argument( '-f', dest='facility' )
parser.add_argument( '-i', dest='instance' )
parser.add_argument( '-v', dest='value' )
parser.add_argument( '-u', dest='units' )

args = parser.parse_args()

# Open the database
cache_db.open_db()

# Write to the database
cache_db.save_value_and_units( args.view, args.facility, args.instance, args.value, args.units )
