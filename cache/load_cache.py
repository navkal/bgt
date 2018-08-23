# Copyright 2018 BACnet Gateway.  All rights reserved.

import argparse
import os
import pandas as pd
import time
from datetime import timedelta

import sys
sys.path.append( '../util' )
from bacnet_gateway_requests import get_bacnet_value
sys.path.append( '../../bg/util' )
import db_util


def load_cache():
    start_time = time.time()

    # Traverse CSV files; each corresponds to one view
    for root, dirs, files in os.walk( '../csv/' ):

        for csv_filename in files:

            # Format view name
            view = os.path.splitext( csv_filename )[0]

            # Report start of view
            db_util.log( logpath, "Loading view '" + view + "'" )
            view_start_time = time.time()

            # Load dataframe representing current view
            df = pd.read_csv( '../csv/' + csv_filename, na_filter=False, comment='#' )

            # Traverse rows in current view
            n_loaded = 0
            for index, view_row in df.iterrows():

                facility = view_row.iloc[1]

                # Traverse instances in current row
                for i in range( 2, len( view_row ) ):

                    instance = view_row.iloc[i]

                    # If instance is not empty, issue BACnet request
                    if instance:
                        time.sleep( args.sleep_interval )
                        get_bacnet_value( facility, instance, args.hostname, args.port )
                        n_loaded += 1

            db_util.log( logpath, "Loaded view '" + view + "' with " + str( n_loaded ) + " values.  Elapsed time: " + str( timedelta( seconds=int( time.time() - view_start_time ) ) ) )

    db_util.log( logpath, 'Loaded all views.  Elapsed time: ' + str( timedelta( seconds=int( time.time() - start_time ) ) ) )



if __name__ == '__main__':

    # Get list of running processes
    ps = os.popen( 'ps -elf' ).read()

    # Find out how many occurrences of this script are running
    dups = ps.count( __file__ )

    # If no other occurrences of this script are running, proceed to update cache
    if dups <= 1:

        # Get command line arguments
        parser = argparse.ArgumentParser( description='Load Building Monitor values into BACnet Gateway cache', add_help=False )
        parser.add_argument( '-h', dest='hostname' )
        parser.add_argument( '-p', dest='port' )
        parser.add_argument( '-s', dest='sleep_interval', type=int )
        args = parser.parse_args()

        logpath = '../../bgt_db/load_cache_' + time.strftime( '%Y-%m-%d_%H-%M-%S', time.localtime() ) + '.log'

        load_cache()
