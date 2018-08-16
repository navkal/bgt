# Copyright 2018 BACnet Gateway.  All rights reserved.

import argparse
import os
import pandas as pd
import time
import cache_db
from datetime import timedelta

import sys
sys.path.append( '../util' )
from bacnet_gateway_requests import get_value_and_units


log_filename = None


def update_cache():

    # Traverse CSV files; each corresponds to one view
    for root, dirs, files in os.walk( '../csv/' ):

        for csv_filename in files:

            # Format view name
            view = os.path.splitext( csv_filename )[0]
            log( "Starting view '" + view + "'" )

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
                        value, units = get_value_and_units( facility, instance, args.hostname, args.port )

                        # If we got value and units, save them in the cache
                        if isinstance( value, ( int, float ) ) and units:
                            cache_db.save_value_and_units( view, facility, instance, value, units )
                            n_saved += 1

            log( 'Saved ' + str( n_saved ) + ' values')


def log( msg ):

    # Format output line
    t = time.localtime()
    s = '[' + time.strftime( '%Y-%m-%d %H:%M:%S', t ) + '] ' + msg

    # Print to standard output
    print( s )

    # Optionally format new log filename
    global log_filename
    if not log_filename or not os.path.exists( log_filename ):
        log_filename = '../../bgt_db/update_cache_' + time.strftime( '%Y-%m-%d_%H-%M-%S', t ) + '.log'

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
            update_cache()
            log( 'Time to update all views: ' + str( timedelta( seconds=int( time.time() - start_time ) ) ) )
