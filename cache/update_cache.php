<?php
  // Copyright 2018 BACnet Gateway.  All rights reserved.
  require_once $_SERVER["DOCUMENT_ROOT"] . "/../common/util.php";

  error_log( "==> post=" . print_r( $_POST, true ) );

  if (
    isset( $_POST['view'] ) &&
    isset( $_POST['facility'] ) &&
    isset( $_POST['instance'] ) &&
    isset( $_POST['value'] ) &&
    isset( $_POST['units'] )
    )
  {
    $sView = $_POST['view'];
    $sFacility = $_POST['facility'];
    $sInstance = $_POST['instance'];
    $sValue = $_POST['value'];
    $sUnits = $_POST['units'];

    error_log( '======> update_cache.php ' . getcwd() );

    // Format command
    // chdir( '..' );
    // $command = quote( getenv( 'PYTHON' ) ) . ' baselines/get_baseline.py 2>&1 -f ' . quote( $sCsvBasename ) . ' -c ' . $sGraphName . ' -t ' . $iTimestamp;

    // Execute command
    // error_log( '==> command=' . $command );
    // exec( $command, $output, $status );
    // error_log( '==> output=' . print_r( $output, true ) );
  }

  echo json_encode( 'done' );
?>
