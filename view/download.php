<?php
  // Copyright 2018 BACnet Gateway.  All rights reserved.

  require_once $_SERVER['DOCUMENT_ROOT'].'/../common/util.php';

  error_log( '==> request=' . print_r( $_REQUEST, true ) );


  // Get view description and cached data
  chdir( $_SERVER['DOCUMENT_ROOT'] );
  $g_sCsvFilename = $_REQUEST['csv_filename'];
  $g_sCsvBasename = basename( $g_sCsvFilename, '.csv' );
  include $_SERVER['DOCUMENT_ROOT'].'/view/common.php';

  $g_aCachedValues = (array) $g_tCachedValues;
  foreach ( $aLines as $sKey => $aLine )
  {
    $sLabel = array_shift( $aLine );
    $sFacility = array_shift( $aLine );
    $aInstances = $aLine;
    error_log( '====> Line=' . $sLabel . ' ' . $sFacility . ' ' . print_r( $aInstances, true ) );

    if ( isset( $g_aCachedValues[$sFacility] ) )
    {
      $aFacility = (array) $g_aCachedValues[$sFacility];
      foreach ( $aInstances as $iInstance )
      {
        if ( isset( $aFacility[$iInstance] ) )
        {
          $aData = (array) $aFacility[$iInstance];
          error_log( '---> ' . $sLabel . ' ' . $aData[$aData['property']] . ' ' . $aData['units'] );
        }
      }
    }
  }

  // Extract the data into arrays of columns and rows
  $aColumns = [];
  $aRows = [];
  foreach ( $g_tCachedValues as $tData )
  {
    foreach ( $tData as $tRow )
    {
      array_push( $aRows, (array) $tRow );

      foreach ( $tRow as $sColumn => $sCell )
      {
        $aColumns[$sColumn] = $sColumn;
      }
    }
  }

  //
  // Generate CSV file representing current view
  //

  // Open the file
  $sPath = sys_get_temp_dir() . '/' . $g_sCsvBasename . '_' . uniqid() . '.csv';
  $tFile = fopen( $sPath, 'w' );

  // Write column headers to the file
  sort( $aColumns );
  fputcsv( $tFile, $aColumns );

  // Write rows to the file
  foreach ( $aRows as $aRow )
  {
    // Build row of cells
    $aCells = [];
    foreach ( $aColumns as $sColumn )
    {
      $aCells[$sColumn] = ( isset( $aRow[$sColumn] ) ) ? ( ( strpos( $aRow[$sColumn], ',' ) === false ) ? $aRow[$sColumn] : quote( $aRow[$sColumn] ) ) : '';;
    }

    // Write row to the file
    fputcsv( $tFile, $aCells );
  }

  // Close the file
  fclose( $tFile );

  // Download the file
  downloadFile( $sPath );

  // Delete the file
  unlink( $sPath );
?>
