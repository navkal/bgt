<?php
  // Copyright 2018 BACnet Gateway.  All rights reserved.

  $sCsvFilename = 'csv/dashboard.csv';
  $sFirstColName = 'Facility';
  $aColNames =
    [
      [
        'value_col_name' => 'Power',
        'units_col_name' => 'Power Units',
      ],
      [
        'value_col_name' => 'Energy',
        'units_col_name' => 'Energy Units',
      ]
    ];
?>

<div class="container-fluid">

  <ul class="nav nav-tabs">
    <li class="active"><a data-toggle="tab" href="#tableTab">Table</a></li>
    <li><a data-toggle="tab" href="#plotTab">Plot</a></li>
  </ul>

  <br/>

  <div class="tab-content">

    <div id="tableTab" class="tab-pane fade in active">
      <?php
        include $_SERVER['DOCUMENT_ROOT'] . '/table/table.php';
      ?>
    </div>

    <div id="plotTab" class="tab-pane fade">
      <?php
        include $_SERVER['DOCUMENT_ROOT'] . '/table/plot.php';
      ?>
    </div>

  </div>
</div>
