# Copyright 2018 BACnet Gateway.  All rights reserved.

import argparse
import os
import pandas as pd
import time
import cache_db
from datetime import timedelta

import sys
sys.path.append( '../../bg/util' )
from bacnet_gateway_requests import get_value


log_filename = None


def load_cache():

    # Traverse CSV files; each corresponds to one view
    for root, dirs, files in os.walk( '../csv/' ):

        for csv_filename in files:

            # Format view name
            view = os.path.splitext( csv_filename )[0]

            # Report start of view
            log( "Loading view '" + view + "'" )
            view_start_time = time.time()

            # Load dataframe representing current view
            df = pd.read_csv( '../csv/' + csv_filename, na_filter=False, comment='#' )

            # Traverse rows in current view
            n_saved = 0
            for index, view_row in df.iterrows():

                facility = view_row.iloc[1]

                # Traverse instances in current row
                for i in range( 2, len( view_row ) ):

                    instance = view_row.iloc[i]

                    # If instance is not empty, issue BACnet request
                    if instance:
                        time.sleep( args.sleep_interval )
                        value, units = get_value( facility, instance, args.hostname, args.port, live=True )

                        # If we got value and units, save them in the cache
                        if isinstance( value, ( int, float ) ) and units:
                            cache_db.save_value_and_units( view, facility, instance, value, units )
                            n_saved += 1

            log( "Loaded view '" + view + "' with " + str( n_saved ) + " values.  Elapsed time: " + str( timedelta( seconds=int( time.time() - view_start_time ) ) ) )


def log( msg ):

    # Format output line
    t = time.localtime()
    s = '[' + time.strftime( '%Y-%m-%d %H:%M:%S', t ) + '] ' + msg

    # Print to standard output
    print( s )

    # Optionally format new log filename
    global log_filename
    if not log_filename or not os.path.exists( log_filename ):
        log_filename = '../../bgt_db/load_cache_' + time.strftime( '%Y-%m-%d_%H-%M-%S', t ) + '.log'

    # Open, write, and close log file
    logfile = open( log_filename , 'a' )
    logfile.write( s + '\n' )
    logfile.close()



if __name__ == '__main__':

    # Get list of running processes
    ps = os.popen( 'ps -elf' ).read()

    # Find out how many occurrences of this script are running
    dups = ps.count( __file__ )

    # If no other occurrences of this script are running, proceed to update cache
    if dups <= 1:

        # Get command line arguments
        parser = argparse.ArgumentParser( description='Maintain cache of recent values used by Building Monitor', add_help=False )
        parser.add_argument( '-h', dest='hostname' )
        parser.add_argument( '-p', dest='port' )
        parser.add_argument( '-s', dest='sleep_interval', type=int )
        parser.add_argument( '-r', dest='remove', action='store_true' )
        args = parser.parse_args()

        # Open the database
        cache_db.open_db( args.remove )

        # Update cache continuously
        while True:
            start_time = time.time()
            load_cache()
            log( 'Loaded all views.  Elapsed time: ' + str( timedelta( seconds=int( time.time() - start_time ) ) ) )
