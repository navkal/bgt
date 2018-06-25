<?php
  // Copyright 2018 BACnet Gateway.  All rights reserved.
?>

<div class="container">
  <div>
    <table id="bgt_table" class="table">

      <thead>
        <tr>
          <th>
            <?=$sFirstColName?>
          </th>

          <?php
            foreach ( $aColNames as $tColNames )
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
  </div>
</div>