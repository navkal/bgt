<?php
  // Copyright 2018 BACnet Gateway.  All rights reserved.

  $sCsvFilename = 'test_sanborn.csv';
  $sFirstColName = 'Location';
  $aColNames =
    [
      [
        'value_col_name' => 'Temperature',
        'units_col_name' => 'Temperature Units',
      ],
      [
        'value_col_name' => 'CO2',
        'units_col_name' => 'CO2 Units',
      ]
    ];

  require_once( 'test_common.php' );
?>
