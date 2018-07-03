<?php
  // Copyright 2018 BACnet Gateway.  All rights reserved.

  $g_sCsvFilename = 'csv/ahs_temperature.csv';
  $g_sFirstColName = 'Location';
  $g_aColNames =
  [
    [
      'value_col_name' => 'Temperature',
      'units_col_name' => 'Units',
      'graph' =>
      [
        'graph_id' => 'temperature',
        'delta' => false
      ]
    ]
  ];

  $g_sLayoutMode = 'split';

  include $_SERVER['DOCUMENT_ROOT'] . '/view/view.php';
?>
