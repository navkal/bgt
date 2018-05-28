<?php
  // Copyright 2018 BACnet Gateway.  All rights reserved.

  @session_start();
  if ( ! isset( $_SESSION['bgt'] ) )
  {
    $_SESSION['bgt'] =
      [
        'host' => isset( $_REQUEST['host'] ) ? $_REQUEST['host'] : '192.168.1.186',
        'port' => isset( $_REQUEST['port'] ) ? $_REQUEST['port'] : '8000'
      ];
  }

  error_log( '==> BACnet Gateway Host: ' . $_SESSION['bgt']['host'] );
  error_log( '==> BACnet Gateway Port: ' . $_SESSION['bgt']['port'] );

  include "../common/main.php";
?>
