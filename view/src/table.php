<?php
  // Copyright 2018 BACnet Gateway.  All rights reserved.
?>

<!-- Attach Download button above table head -->
<div class="tablesorter-dropbox">
  <div class="btn tablesorter-headerRow" style="width:100%; border-bottom:0px; padding: 1px 5px;" >
    <span class="float-right">
      <button id="refreshButton" class="btn btn-sm btn-link" onclick="toggleRefresh()" disabled >
        <i id="startRefreshIcon" class="fas fa-sync-alt text-success"></i>
        <i id="stopRefreshIcon" class="far fa-stop-circle text-danger" style="display:none"></i>
        Refresh
      </button>
      <button class="btn btn-sm btn-link" onclick="uploadSnapshot()">
        <i class="fas fa-download"></i> Download
      </button>
    </span>
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
