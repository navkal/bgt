<?php
  // Copyright 2018 BACnet Gateway.  All rights reserved.

  @session_start();
  $_SESSION['bgt'] =
    [
      'host' => isset( $_REQUEST['host'] ) ? $_REQUEST['host'] : '192.168.1.186',
      'port' => isset( $_REQUEST['port'] ) ? $_REQUEST['port'] : '8000'
    ];

  include "../common/main.php";
?>
