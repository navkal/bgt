<?php
  // Copyright 2018 BACnet Gateway.  All rights reserved.
  require_once $_SERVER["DOCUMENT_ROOT"] . "/../common/util.php";

  error_log( "==> post=" . print_r( $_POST, true ) );

  $tResult = [];
  if ( count( $_POST ) > 0 )
  {
    $sCsvBasename = $_POST['csv_basename'];
    $sGraphName = $_POST['graph_name'];
    $iTimestamp = $_POST['timestamp'];

    // Format command
    chdir( '..' );
    $command = quote( getenv( 'PYTHON' ) ) . ' baselines/get_baseline.py 2>&1 -f ' . quote( $sCsvBasename ) . ' -c ' . $sGraphName . ' -t ' . $iTimestamp;

    // Execute command
    error_log( '==> command=' . $command );
    exec( $command, $output, $status );
    error_log( '==> output=' . print_r( $output, true ) );

    // Return graph name with result
    $sResult = $output[ count( $output ) - 1 ];
    $tResult = json_decode( $sResult );
    $tResult->graph_name = $sGraphName;
    $aResult = (array) $tResult;
    ksort( $aResult );
  }

  echo json_encode( $aResult );
?>
