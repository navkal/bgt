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
        <span class="float-right">
          <a class="bgt_table_temperature_button btn btn-sm btn-link" title="AHS Weather Station" target="_blank" href="https://owc.enterprise.earthnetworks.com/OnlineWeatherCenter.aspx?aid=5744" >
            <i class="fas fa-cloud-sun"></i>
            <span class="bgt_table_temperature_value">&nbsp;&nbsp;</span>&nbsp;&deg;F
          </a>
        </span>
      </th>
    </tr>

    <!-- Toolbar -->
    <tr>
      <th class="sorter-false tablesorter-headerRow" colspan="<?=$iColspan?>" >
        <span class="btn btn-sm" style="cursor:default;" >
          <span class="bgt_table_row_count"></span> row<span id="bgt_table_row_count_s">s</span>
        </span>
        <span class="float-right">
          <button class="bgt_table_refresh_button btn btn-sm btn-link" onclick="toggleRefresh()" disabled >
            <i class="bgt_table_start_refresh_icon fas fa-sync-alt text-success"></i>
            <i class="bgt_table_stop_refresh_icon far fa-stop-circle text-danger" style="display:none"></i>
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
      <th class="sortable sorter-false sorter-firstcol">
        <?=$g_sFirstColName?>
      </th>

      <?php
        foreach ( $g_aColNames as $tColNames )
        {
      ?>

          <th class="sortable sorter-false" style="text-align:right">
            <?=$tColNames['value_col_name']?>
          </th>
          <th class="sortable sorter-false">
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
