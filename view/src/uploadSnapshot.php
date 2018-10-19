<?php
  // Copyright 2018 BACnet Gateway.  All rights reserved.

  require_once $_SERVER['DOCUMENT_ROOT'].'/../common/util.php';
  require_once $_SERVER['DOCUMENT_ROOT'].'/view/src/makeSnapshotPath.php';

  error_log( '==> post=' . print_r( $_POST, true ) );

  $sSnapshotId = "parameter(s) missing";

  if ( isset( $_POST['csv_basename'], $_POST['snapshot'] ) )
  {
    // Extract parameters
    $sCsvBasename = $_POST['csv_basename'];
    $sSnapshot = $_POST['snapshot'];

    // Open snapshot file
    $sSnapshotId = uniqid();
    $sPath = makeSnapshotPath( $sCsvBasename, $sSnapshotId );
    $tFile = fopen( $sPath, 'w' );

    // Write rows to snapshot file
    $aRows = json_decode( $sSnapshot );
    foreach ( $aRows as $aRow )
    {
      fputcsv( $tFile, $aRow );
    }

    // Close snapshot file
    fclose( $tFile );
  }

  echo json_encode( $sSnapshotId );
?>
