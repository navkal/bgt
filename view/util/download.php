<?php
  // Copyright 2018 BACnet Gateway.  All rights reserved.

  require_once $_SERVER['DOCUMENT_ROOT'].'/../common/util.php';

  error_log( '==> request=' . print_r( $_REQUEST, true ) );

  if ( isset( $_REQUEST['csv_basename'] ) )
  {
    $g_sCsvBasename = $_REQUEST['csv_basename'];

    $sLayoutScript = $_SERVER['DOCUMENT_ROOT'].'/view/layout/' . $g_sCsvBasename . '_layout.php';

    if ( file_exists( $sLayoutScript ) )
    {
      // Get layout parameters
      include $sLayoutScript;

      // Get view description and cached data
      chdir( $_SERVER['DOCUMENT_ROOT'] );
      include $_SERVER['DOCUMENT_ROOT'].'/view/util/common.php';

      //
      // Build spreadsheet
      //

      $g_aCachedValues = (array) $g_tCachedValues;

      // Initialize empty spreadsheet content
      $aHead = [];
      $aRows = [];

      // Traverse lines of content definition CSV file
      foreach ( $aLines as $aLine )
      {
        // Extract label, facility, and instances from content definition line
        $sLabel = array_shift( $aLine );
        $sFacility = array_shift( $aLine );
        $aInstances = $aLine;

        // Find cached data corresponding to content definition line
        if ( isset( $g_aCachedValues[$sFacility] ) )
        {
          $aCachedFacility = (array) $g_aCachedValues[$sFacility];
          $aRow = [ $sLabel ];
          $aTimestamps = [];

          // Traverse instances listed in content definition line
          foreach ( $aInstances as $iOffset => $iInstance )
          {
            // Load column names into spreadsheet head
            if ( count( $aHead ) < count( $aInstances ) * 2 )
            {
              $aColNames = (array) $g_aColNames[$iOffset];
              array_push( $aHead, $aColNames['value_col_name'] );
              array_push( $aHead, $aColNames['units_col_name'] );
            }

            // Look for current instance in cached data for this facility
            if ( isset( $aCachedFacility[$iInstance] ) )
            {
              // Load cached data into spreadsheet
              $aData = (array) $aCachedFacility[$iInstance];
              array_push( $aRow, formatValue( $aData[$aData['property']] ) );
              array_push( $aRow, $aData['units'] );
              array_push( $aTimestamps, $aData['timestamp'] );
            }
            else
            {
              // Load empty cells into spreadsheet
              array_push( $aRow, '' );
              array_push( $aRow, '' );
            }
          }

          // Load the latest timestamp into row
          $sTimestamp = count( $aTimestamps ) ? strftime( '%m/%d/%Y, %I:%M:%S %p', intval( max( $aTimestamps ) / 1000 ) ) : '';
          array_push( $aRow, $sTimestamp );

          // Load row into spreadsheet
          array_push( $aRows, $aRow );
        }
      }

      // Add first and last column names
      array_unshift( $aHead, $g_sFirstColName );
      array_push( $aHead, UPDATE_TIME );

      //
      // Dump spreadsheet to CSV file
      //

      // Open the file
      $sPath = sys_get_temp_dir() . '/' . $g_sCsvBasename . '_' . uniqid() . '.csv';
      error_log( '===> download.php opening file <' . $sPath . '>' );
      $tFile = fopen( $sPath, 'w' );

      // Write column headers to the file
      fputcsv( $tFile, $aHead );

      // Write rows to the file
      foreach ( $aRows as $aRow )
      {
        fputcsv( $tFile, $aRow );
      }

      // Close the file
      fclose( $tFile );

      // Download the file
      downloadFile( $sPath );

      // Delete the file
      ////////////unlink( $sPath );
    }
  }


/////////


  function formatValue( $rawValue )
  {
    // Decide how to display the value
    if ( $rawValue === '' )
    {
      $value = '';
    }
    else if ( ( -1 < $rawValue ) && ( $rawValue < 1 ) )
    {
      $value = round( $rawValue * 100 ) / 100;
    }
    else
    {
      $value = round( $rawValue );
    }

    return $value;
  }
?>
