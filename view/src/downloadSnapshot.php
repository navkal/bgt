<?php
  // Copyright 2018 Building Energy Monitor.  All rights reserved.

  require_once $_SERVER['DOCUMENT_ROOT'].'/../common/util.php';
  require_once $_SERVER['DOCUMENT_ROOT'].'/view/src/makeSnapshotPath.php';

  error_log( '==> request=' . print_r( $_REQUEST, true ) );

  if ( isset( $_REQUEST['csv_basename'], $_REQUEST['snapshot_id'] ) )
  {
    // Extract parameters
    $sCsvBasename = $_REQUEST['csv_basename'];
    $sSnapshotId = $_REQUEST['snapshot_id'];

    // Format path of snapshot file
    $sPath = makeSnapshotPath( $sCsvBasename, $sSnapshotId );

    // Download snapshot file
    downloadFile( $sPath );

    // Delete snapshot file
    unlink( $sPath );
  }
?>
