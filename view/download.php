<?php
  // Copyright 2018 BACnet Gateway.  All rights reserved.

  require_once $_SERVER["DOCUMENT_ROOT"]."/../common/util.php";

  chdir( $_SERVER["DOCUMENT_ROOT"] );


  $g_sCsvFilename = $_SESSION['bgt']['view'];
  $g_sCsvBasename = basename( $g_sCsvFilename, '.csv' );

  //
  // Retrieve values from cache
  //

  // Format command
  $command = quote( getenv( 'PYTHON' ) ) . ' ' . quote( $_SERVER["DOCUMENT_ROOT"].'/cache/get_view.py' ) . ' 2>&1'
    . ' -v ' . quote( $g_sCsvBasename )
    . ' -h ' . $_SESSION['bgt']['host']
    . ' -p ' . $_SESSION['bgt']['port'];

  // Execute command
  error_log( '==> command=' . $command );
  exec( $command, $output, $status );
  error_log( '==> output=' . print_r( $output, true ) );
  $g_tCachedValues = json_decode( $output[ count( $output ) - 1 ] );

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
