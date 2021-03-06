<?php
  // Copyright 2018 Building Energy Monitor.  All rights reserved.

  $g_sLayoutMode = $g_sLayoutModeDefault;

  $g_sCsvFilename = 'csv/aps_electricity.csv';
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

  include $_SERVER['DOCUMENT_ROOT'] . '/view/src/view.php';
?>
