<?php
  // Copyright 2018 BACnet Gateway.  All rights reserved.

  $sCsvFilename = 'csv/ahs_co2.csv';
  $sFirstColName = 'Location';
  $aColNames =
    [
      [
        'value_col_name' => 'CO2 Level',
        'units_col_name' => 'Units'
      ]
    ];

  include $_SERVER['DOCUMENT_ROOT'] . '/table/datamix.php';
?>
