<?php
  // Copyright 2018 BACnet Gateway.  All rights reserved.
?>

<!-- Attach Download button above table head -->
<div class="tablesorter-dropbox">
  <div class="btn tablesorter-headerRow" style="width:100%; border-bottom:0px" >
    <button onclick="uploadSnapshot()" class="btn btn-xs btn-link float-right">
      <span class="glyphicon glyphicon-download-alt"></span> Download
    </button>
  </div>
</div>


<table id="bgt_table" class="table" style="display:none" >

  <thead>
    <tr>
      <th>
        <?=$g_sFirstColName?>
      </th>

      <?php
        foreach ( $g_aColNames as $tColNames )
        {
      ?>

          <th style="text-align:right">
            <?=$tColNames['value_col_name']?>
          </th>
          <th>
            <?=$tColNames['units_col_name']?>
          </th>

      <?php
        }
      ?>

      <th>
        <?=UPDATE_TIME?>
      </th>
    </tr>
  </thead>

  <tbody>
  </tbody>

</table>
