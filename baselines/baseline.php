<?php
  // Copyright 2018 BACnet Gateway.  All rights reserved.
  require_once $_SERVER["DOCUMENT_ROOT"] . "/../common/util.php";

  error_log( "==> post=" . print_r( $_POST, true ) );

  $tResult = [];
  if ( count( $_POST ) > 0 )
  {
    $sCsvBasename = $_POST['csv_basename'];
    $sGraphName = $_POST['graph_name'];
    $sBaselinePick = $_POST['baseline_pick'];

    switch( $sBaselinePick )
    {
      case 'day':
        $fTimestamp = microtime( true ) * 1000;
        break;

      case 'week':
      case 'month':
      case 'year':









        $fTimestamp = microtime( true ) * 1000;
        break;
    }

    // Format command
    chdir( '..' );
    $command = quote( getenv( 'PYTHON' ) ) . ' baselines/get_baseline.py 2>&1 -f ' . quote( $sCsvBasename ) . ' -c ' . $sGraphName . ' -t ' . $fTimestamp;

    // Execute command
    error_log( '==> command=' . $command );
    exec( $command, $output, $status );
    error_log( '==> output=' . print_r( $output, true ) );
    $tResult = $output[ count( $output ) - 1 ];
  }

  echo json_encode( $tResult );
?>
