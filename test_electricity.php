<?php
  // Copyright 2018 BACnet Gateway.  All rights reserved.

  $sCsvFilename = 'test_electricity.csv';
  $sFirstColName = 'Feeder';
  $aInstanceColNames =
    [
      [
        'value_col_name' => 'Meter Reading',
        'units_col_name' => 'Units',
      ]
    ];

  require_once( 'test_common.php' );
?>
