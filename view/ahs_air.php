<?php
  // Copyright 2018 BACnet Gateway.  All rights reserved.

  $g_sCsvFilename = 'csv/ahs_air.csv';
  $g_sFirstColName = 'Location';
  $g_aColNames =
    [
      [
        'value_col_name' => 'Temperature',
        'units_col_name' => 'Temperature Units',
        'graph' =>
          [
            'graph_id' => 'temperature',
            'delta' => false
          ]
      ],
      [
        'value_col_name' => 'CO2',
        'units_col_name' => 'CO2 Units',
        'graph' =>
          [
            'graph_id' => 'co2',
            'delta' => false
          ]
      ]
    ];

  include $_SERVER['DOCUMENT_ROOT'] . '/view/view.php';
?>
