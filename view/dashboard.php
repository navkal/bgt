<?php
  // Copyright 2018 BACnet Gateway.  All rights reserved.

  $g_sCsvFilename = 'csv/dashboard.csv';
  $g_sFirstColName = 'Facility';
  $g_aColNames =
  [
    [
      'value_col_name' => 'Power',
      'units_col_name' => 'Power Units',
      'graph' =>
      [
        'graph_id' => 'power'
      ]
    ],
    [
      'value_col_name' => 'Energy',
      'units_col_name' => 'Energy Units',
      'graph' =>
      [
        'graph_id' => 'energy'
      ]
    ]
  ];

  $g_sLayoutMode = $g_sLayoutModeDefault;

  include $_SERVER['DOCUMENT_ROOT'] . '/view/view.php';
?>
