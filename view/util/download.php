<?php
  // Copyright 2018 BACnet Gateway.  All rights reserved.

  require_once $_SERVER['DOCUMENT_ROOT'].'/../common/util.php';

  error_log( '==> request=' . print_r( $_REQUEST, true ) );

  if ( isset( $_REQUEST['csv_basename'] ) )
  {
    // Get view description and cached data
    chdir( $_SERVER['DOCUMENT_ROOT'] );
    include $_SERVER['DOCUMENT_ROOT'].'/view/layout/' . $_REQUEST['csv_basename'] . '.php';
    include $_SERVER['DOCUMENT_ROOT'].'/view/util/common.php';

    // Use view description and cached data to build spreadsheet
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
        $sTest = '--> ' . $sLabel. ':';
        foreach ( $aInstances as $iOffset => $iInstance )
        {
          if ( isset( $aFacility[$iInstance] ) )
          {
            $aData = (array) $aFacility[$iInstance];
            $aColNames = (array) $g_aColNames[$iOffset];
            $sTest .= ' ' . $aColNames['value_col_name'] . '=' . $aData[$aData['property']] . ' ' . $aColNames['units_col_name'] . '=' . $aData['units'];
          }
        }
        error_log( $sTest );
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
  }
?>
