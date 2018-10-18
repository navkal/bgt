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

  define( 'LAYOUT_MODE_TAB', 'tab' );
  define( 'LAYOUT_MODE_SPLIT', 'split' );
  $g_sLayoutModeDefault = LAYOUT_MODE_SPLIT;

  // Load jQuery library outside the document head.  split.js needs this; don't know why.
  define( 'BOOTSTRAP_VERSION', /**'_4'/**/ /**/''/**/ );
  require_once '../common/libraries' . BOOTSTRAP_VERSION . '.php';  // <-- Includes jQuery, redundant with document head; needed by split.js (don't know why)

  include "../common/main.php";

  if ( $_SESSION['bgt']['bgt_'] )
  {
?>
    <link rel="stylesheet" href="bgt_.css?version=<?=time()?>">
<?php
  }
?>
