<?php
  // Copyright 2018 BACnet Gateway.  All rights reserved.

  $sCsvFilename = 'test_co2.csv';
  $sFirstColName = 'Location';
  $aInstanceColNames =
    [
      [
        'value_col_name' => 'CO2 Level',
        'units_col_name' => 'Units',
      ]
    ];

  require_once( 'test_common.php' );
?>
