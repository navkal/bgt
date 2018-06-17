<?php
  // Copyright 2018 BACnet Gateway.  All rights reserved.

  $sCsvFilename = 'csv/test_electricity.csv';
  $sFirstColName = 'Feeder';
  $aColNames =
    [
      [
        'value_col_name' => 'Meter Reading',
        'units_col_name' => 'Units',
      ]
    ];

  require_once( 'test_common.php' );
?>
