<?php
  // Copyright 2018 BACnet Gateway.  All rights reserved.

  $sCsvFilename = 'test_dashboard.csv';
  $sFirstColName = 'Facility';
  $aColNames =
    [
      [
        'value_col_name' => 'Power',
        'units_col_name' => 'Power Units',
      ],
      [
        'value_col_name' => 'Energy',
        'units_col_name' => 'Energy Units',
      ]
    ];

  require_once( 'test_common.php' );
?>