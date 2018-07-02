<?php
  // Copyright 2018 BACnet Gateway.  All rights reserved.
?>

<!-- This inclusion of jQuery library is redundant with the one included in the document head, but needed by split.js. (Don't know why.) -->
<script src="https://code.jquery.com/jquery-2.2.4.min.js" integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44=" crossorigin="anonymous"></script>

<?php
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
