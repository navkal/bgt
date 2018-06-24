<?php
  // Copyright 2018 BACnet Gateway.  All rights reserved.

  $sCsvFilename = 'csv/ahs_electricity.csv';
  $sFirstColName = 'Feeder';
  $aColNames =
    [
      [
        'value_col_name' => 'Meter Reading',
        'units_col_name' => 'Units'
      ]
    ];

  include $_SERVER['DOCUMENT_ROOT'] . '/table/datamix.php';
?>
