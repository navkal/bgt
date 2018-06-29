<?php
  // Copyright 2018 BACnet Gateway.  All rights reserved.

  $g_sCsvFilename = 'csv/ahs_electricity.csv';
  $g_sFirstColName = 'Feeder';
  $g_aColNames =
    [
      [
        'value_col_name' => 'Power',
        'units_col_name' => 'Power Units',
        'graph' =>
          [
            'graph_id' => 'power',
            'delta' => false
          ]
      ],
      [
        'value_col_name' => 'Energy',
        'units_col_name' => 'Energy Units',
        'graph' =>
          [
            'graph_id' => 'energy',
            'delta' => true
          ]
      ]
    ];

  include $_SERVER['DOCUMENT_ROOT'] . '/view/view.php';
?>
