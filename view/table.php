<?php
  // Copyright 2018 BACnet Gateway.  All rights reserved.
?>

<!-- Attach Download button above table head -->
<div class="tablesorter-dropbox">
  <div class="btn btn-sm tablesorter-headerRow" style="width:100%; border-bottom:0px" >
    <a href="view/download.php?view=<?=$g_sCsvFilename?>" class="btn btn-xs pull-right">
      <span class="glyphicon glyphicon-download-alt"></span> Download
    </a>
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
        Update Time
      </th>
    </tr>
  </thead>

  <tbody>
  </tbody>

</table>
