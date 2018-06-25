<?php
  // Copyright 2018 BACnet Gateway.  All rights reserved.

  $g_sCsvFilename = 'csv/ahs_co2.csv';
  $g_sFirstColName = 'Location';
  $g_aColNames =
    [
      [
        'value_col_name' => 'CO2 Level',
        'units_col_name' => 'Units'
      ]
    ];

  include $_SERVER['DOCUMENT_ROOT'] . '/view/view.php';
?>
