<?php
  // Copyright 2018 BACnet Gateway.  All rights reserved.
?>

<table id="bgt_table" class="table">

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
        Update Time
      </th>
    </tr>
  </thead>

  <tbody>
  </tbody>

</table>
