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
    // Format command
    error_log( '==========> PHP_OS=' . PHP_OS . ' PHP_OS_FAMILY=' . PHP_OS_FAMILY );
    $sudo = ( PHP_OS_FAMILY == 'Linux' ) ? 'sudo ' : '';
    error_log( '====> sudo=' . $sudo );
    $command = $sudo . quote( getenv( 'PYTHON' ) ) . ' write_cache.py 2>&1'
      . ' -w ' . quote( $_POST['view'] )
      . ' -f ' . quote( $_POST['facility'] )
      . ' -i ' . quote( $_POST['instance'] )
      . ' -v ' . quote( $_POST['value'] )
      . ' -u ' . quote( $_POST['units'] );

    // Execute command
    error_log( '==> command=' . $command );
    exec( $command, $output, $status );
    error_log( '==> output=' . print_r( $output, true ) );
  }

  echo json_encode( 'done' );
?>
