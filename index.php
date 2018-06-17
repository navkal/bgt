<?php
  // Copyright 2018 BACnet Gateway.  All rights reserved.

  @session_start();
  if ( ! isset( $_SESSION['bgt'] ) )
  {
    $_SESSION['bgt'] =
      [
        'host' => isset( $_REQUEST['host'] ) ? $_REQUEST['host'] : ( $_SERVER['SERVER_ADDR'] == '::1' ? '127.0.0.1' : $_SERVER['SERVER_ADDR'] ),
        'port' => isset( $_REQUEST['port'] ) ? $_REQUEST['port'] : '8000'
      ];
  }

  error_log( '==> BACnet Gateway Host: ' . $_SESSION['bgt']['host'] );
  error_log( '==> BACnet Gateway Port: ' . $_SESSION['bgt']['port'] );

  $_SESSION['bgt']['bgt_'] = strpos( $_SERVER['DOCUMENT_ROOT'], '/bgt_' ) !== false;

  include "../common/main.php";

  if ( $_SESSION['bgt']['bgt_'] )
  {
?>
    <link rel="stylesheet" href="bgt_.css?version=<?=time()?>">
<?php
  }
?>

<style>
  .bg-dropbox
  {
    background-color: #f1f9ff;
    border: 1px solid #8dd0fc;
  }
</style>

<!-- tablesorter theme -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery.tablesorter/2.29.0/css/theme.dropbox.min.css" integrity="sha256-VFOuP1wPK9H/EeQZEmYL0TZlkMtUthqMBdrqfopliF4=" crossorigin="anonymous" />

<!-- tablesorter basic libraries -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.tablesorter/2.29.0/js/jquery.tablesorter.min.js" integrity="sha256-UD/M/6ixbHIPJ/hTwhb9IXbHG2nZSiB97b4BSSAVm6o=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.tablesorter/2.29.0/js/jquery.tablesorter.widgets.min.js" integrity="sha256-/3WKCLORjkqCd7cddzHbnXGR31qqys81XQe2khfPvTY=" crossorigin="anonymous"></script>
