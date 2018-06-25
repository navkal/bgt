<?php
  // Copyright 2018 BACnet Gateway.  All rights reserved.

  $g_sCsvFilename = 'csv/ahs_temperature.csv';
  $g_sFirstColName = 'Location';
  $g_aColNames =
    [
      [
        'value_col_name' => 'Temperature',
        'units_col_name' => 'Units'
      ]
    ];

  include $_SERVER['DOCUMENT_ROOT'] . '/view/view.php';
?>
