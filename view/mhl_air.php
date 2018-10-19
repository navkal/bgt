<?php
  // Copyright 2018 BACnet Gateway.  All rights reserved.

  $g_sLayoutMode = $g_sLayoutModeDefault;

  $g_sCsvFilename = 'csv/mhl_air.csv';
  $g_sFirstColName = 'Location';
  $g_aColNames =
  [
    [
      'value_col_name' => 'Temperature',
      'units_col_name' => 'Temperature Units',
      'graph' =>
      [
        'graph_id' => 'temperature'
      ]
    ],
    [
      'value_col_name' => 'CO2',
      'units_col_name' => 'CO2 Units',
      'graph' =>
      [
        'graph_id' => 'co2'
      ]
    ]
  ];

  include $_SERVER['DOCUMENT_ROOT'] . '/view/src/view.php';
?>
