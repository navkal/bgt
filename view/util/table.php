<?php
  // Copyright 2018 BACnet Gateway.  All rights reserved.
?>

<!-- Attach Download button above table head -->
<div class="tablesorter-dropbox">
  <div class="btn btn-sm tablesorter-headerRow" style="width:100%; border-bottom:0px" >
    <a href="view/util/download.php?csv_basename=<?=$g_sCsvBasename?>" class="btn btn-xs pull-right" title="Download table from cache">
      <span class="glyphicon glyphicon-download-alt"></span> Cache
    </a>
    <button onclick="uploadSnapshot()" class="btn btn-xs btn-link pull-right" title="Download snapshot of view">
      <span class="glyphicon glyphicon-download-alt"></span> Snapshot
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

<script>
  function uploadSnapshot()
  {
    var aRows = [];

    // Extract table head
    var aTh = $( '#bgt_table thead tr th' );
    var aRow = [];
    for ( var i = 0; i < aTh.length; i ++ )
    {
      aRow.push( $( aTh[i] ).text().trim() );
    }
    aRows.push( aRow );

    // Extract table body
    var aTr = $( '#bgt_table tbody tr:not(.filtered)' );
    for ( var i = 0; i < aTr.length; i ++ )
    {
      // Extract table row
      var aTd = $( aTr[i] ).find( 'td' );
      var aRow = [];
      for ( var j = 0; j < aTd.length; j ++ )
      {
        var sTd = $( aTd[j] ).text();
        aRow.push( sTd );
      }
      aRows.push( aRow );
    }

    // Set post arguments
    var tPostData = new FormData();
    tPostData.append( 'csv_basename', g_sCsvBasename );
    tPostData.append( 'snapshot', JSON.stringify( aRows ) );

    // Post request to server
    $.ajax(
      '/view/util/uploadSnapshot.php',
      {
        type: 'POST',
        processData: false,
        contentType: false,
        dataType : 'json',
        data: tPostData
      }
    )
    .done( uploadSnapshotDone )
    .fail( handleAjaxError );
  }

  function uploadSnapshotDone( tRsp, sStatus, tJqXhr )
  {
    window.location.href='view/util/downloadSnapshot.php?csv_basename=' + g_sCsvBasename + '&snapshot_id=' + tRsp;
  }
</script>
