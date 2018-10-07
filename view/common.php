<?php
  // Copyright 2018 BACnet Gateway.  All rights reserved.

  //////////////////////////////////////////////////////////////////
  // Common script segment to build view-specific data structures //
  //////////////////////////////////////////////////////////////////

  //
  // Get view description from CSV file
  //

  // Read description file
  $file = fopen( $g_sCsvFilename, 'r' );
  fgetcsv( $file );

  // Save CSV data in array
  $aLines = [];
  while( ! feof( $file ) )
  {
    $aLine = fgetcsv( $file );
    if ( is_array( $aLine ) && ( count( $aLine ) > 1 ) && ( $aLine[0][0] != '#' ) )
    {
      // Strip out single and double quotes
      $aLine[0] = str_replace( "'", '', $aLine[0] );
      $aLine[0] = str_replace( '"', '', $aLine[0] );

      // Save the line
      array_push( $aLines, $aLine );
    }
  }
  fclose( $file );

  // Sort lines
  usort( $aLines, "compareLines" );
  function compareLines( $aLine1, $aLine2 )
  {
    return strcmp( $aLine1[0], $aLine2[0] );
  }


  //
  // Retrieve values from cache
  //

  // Format command
  $command = quote( getenv( 'PYTHON' ) ) . ' ' . quote( $_SERVER['DOCUMENT_ROOT'].'/cache/get_view.py' ) . ' 2>&1'
    . ' -v ' . quote( $g_sCsvBasename )
    . ' -h ' . $_SESSION['bgt']['host']
    . ' -p ' . $_SESSION['bgt']['port'];

  // Execute command
  error_log( '==> command=' . $command );
  exec( $command, $output, $status );
  error_log( '==> output=' . print_r( $output, true ) );
  $g_tCachedValues = json_decode( $output[ count( $output ) - 1 ] );
?>