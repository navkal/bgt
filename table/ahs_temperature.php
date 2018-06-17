<?php
  // Copyright 2018 BACnet Gateway.  All rights reserved.

  $sCsvFilename = 'csv/ahs_temperature.csv';
  $sFirstColName = 'Location';
  $aColNames =
    [
      [
        'value_col_name' => 'Temperature',
        'units_col_name' => 'Units',
      ]
    ];

  require_once( 'test_common.php' );
?>
