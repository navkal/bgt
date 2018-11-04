<?php
  // Copyright 2018 Building Energy Monitor.  All rights reserved.

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
  //$_SESSION['bgt']['bgt_'] = true; // <------ Uncomment this line to test P&F interface

  define( 'BOOTSTRAP_VERSION', '_4' );

  define( 'LAYOUT_MODE_TAB', 'tab' );
  define( 'LAYOUT_MODE_SPLIT', 'split' );
  $g_sLayoutModeDefault = LAYOUT_MODE_SPLIT;

  // Load jQuery library outside the document head.  split.js needs this; don't know why.
  require_once '../common/libraries' . BOOTSTRAP_VERSION . '.php';  // <-- Includes jQuery, redundant with document head; needed by split.js (don't know why)

  if ( $_SESSION['bgt']['bgt_'] )
  {
    define( 'NAVBAR_EXPAND_CLASS', 'navbar-expand-custom' );
?>
    <!-- CSS for P&F interface -->
    <link rel="stylesheet" href="bgt_.css?version=<?=time()?>">
<?php
  }
?>

<!-- General CSS -->
<link rel="stylesheet" href="bgt.css?version=<?=time()?>">

<?php
  include "../common/main.php";
?>
