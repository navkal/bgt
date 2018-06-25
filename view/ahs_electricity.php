<?php
  // Copyright 2018 BACnet Gateway.  All rights reserved.

  $g_sCsvFilename = 'csv/ahs_electricity.csv';
  $g_sFirstColName = 'Feeder';
  $g_aColNames =
    [
      [
        'value_col_name' => 'Meter Reading',
        'units_col_name' => 'Units'
      ]
    ];

  include $_SERVER['DOCUMENT_ROOT'] . '/view/view.php';
?>
