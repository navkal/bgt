<?php
  // Copyright 2018 Building Monitor.  All rights reserved.
?>

<table id="bgt_table" class="table" style="display:none" >

  <thead>

    <!-- Toolbar -->
    <tr>
      <th class="sorter-false tablesorter-headerRow" colspan="<?=2 + ( count( $g_aColNames ) * 2 )?>" >
        <span class="float-left">
            <span class="btn btn-sm" style="color:#2281cf;" >
              <span id="bgt_table_row_count"></span> row<span id="bgt_table_row_count_s">s</span>
            </span>
        </span>
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
      </th>
    </tr>

    <!-- Column headers -->
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
