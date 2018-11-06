<?php
  // Copyright 2018 Building Energy Monitor.  All rights reserved.

  global $navbarItems;
  global $navbarItemKey;
  $iColspan = 2 + ( count( $g_aColNames ) * 2 );
?>

<table id="bgt_table" class="table" style="display:none" >

  <thead>

    <!-- Title -->
    <tr>
      <th class="sorter-false tablesorter-headerRow" colspan="<?=$iColspan?>" >
        <span class="btn btn-sm" style="cursor:default; font-size:0.9375rem" >
          <?=$navbarItems[$navbarItemKey][0]?>
        </span>
      </th>
    </tr>

    <!-- Toolbar -->
    <tr>
      <th class="sorter-false tablesorter-headerRow" colspan="<?=$iColspan?>" >
        <span class="btn btn-sm" style="cursor:default;" >
          <span id="bgt_table_row_count"></span> row<span id="bgt_table_row_count_s">s</span>
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
      <th class="sorter-firstcol">
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
