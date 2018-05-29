<?php
  // Copyright 2018 BACnet Gateway.  All rights reserved.

  // Read CSV file containing list instances
  $file = fopen( $sCsvFilename, 'r' );
  fgetcsv( $file );

  // Save CSV data in array
  $aInstances = [];
  while( ! feof( $file ) )
  {
    $aInstance = fgetcsv( $file );
    if ( is_array( $aInstance ) && ( count( $aInstance ) > 1 ) )
    {
      array_push( $aInstances, $aInstance );
    }
  }

  // Convert to JSON
  $sInstances = json_encode( $aInstances );

  fclose( $file );
?>

<style>
  .spinner
  {
    position: absolute;
    left: 50%;
    top: 50%;
    z-index: 1051;
    margin: -75px 0 0 -75px;
    border: 16px solid #0079c2;
    border-radius: 50%;
    border-top: 16px solid #8dc63f;
    width: 100px;
    height: 100px;
    -webkit-animation: spin 2s linear infinite;
    animation: spin 2s linear infinite;
  }

  @-webkit-keyframes spin
  {
    0% { -webkit-transform: rotate(0deg); }
    100% { -webkit-transform: rotate(360deg); }
  }

  @keyframes spin
  {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
  }
</style>

<script>

  var g_aInstances = null;
  var g_iValueUnitPairs = 0;
  var g_iPair = 0;
  var g_iInstance = 0;
  var g_iTimeoutMs = 0;
  var g_sPrevValue = '';
  var g_sPrevUnits = '';
  var g_sPrevTime = '';

  $( document ).ready( onDocumentReady );

  function onDocumentReady()
  {
    clearWaitCursor();

    // Load list of instances
    g_aInstances = JSON.parse( '<?=$sInstances?>' );

    // Determine how many value-unit pairs to display
    g_iValueUnitPairs = ( g_aInstances[0].length - 1 )

    // Initialize table
    var sHtml = '';
    for ( var iInstance in g_aInstances )
    {
      sHtml += '<tr>';

      // Create cell for label in first column
      sHtml += '<td>' + g_aInstances[iInstance][0] + '</td>';

      // Create cells for value-unit pairs
      for ( var iPair = 1; iPair <= g_iValueUnitPairs; iPair ++ )
      {
        sHtml += '<td id="value_' + iInstance + '_' + iPair + '" style="text-align:right" ></td>';
        sHtml += '<td id="units_' + iInstance + '_' + iPair + '"></td>';
      }

      // Create cell for time
      sHtml += '<td id="time_' + iInstance + '"></td>';
      sHtml += '</tr>';
    }

    $( '#bgt_table_body' ).html( sHtml );

    // Issue first request
    g_iPair = 1;
    rq();
  }

  function rq()
  {
    setWaitCursor();

    // Save previous values pertaining to current instance
    g_sPrevValue = $( '#value_' + g_iInstance + '_' + g_iPair ).text();
    g_sPrevUnits = $( '#units_' + g_iInstance + '_' + g_iPair ).text();
    g_sPrevTime = $( '#time_' + g_iInstance ).text();

    // Show current row as pending
    $( '#value_' + g_iInstance + '_' + g_iPair ).html( '-' );
    $( '#units_' + g_iInstance + '_' + g_iPair ).html( '-' );
    $( '#time_' + g_iInstance ).html( '-' );

    var sArgList =
        '?facility=ahs'
      + '&instance=' + g_aInstances[g_iInstance][1];

    // Issue request to BACnet Gateway
    $.ajax(
      'http://<?=$_SESSION['bgt']['host']?>:<?=$_SESSION['bgt']['port']?>/' + sArgList,
      {
        method: 'GET',
        processData: false,
        contentType: false,
        dataType : 'jsonp'
      }
    )
    .done( readDone )
    .fail( readFail );
  }

  function readDone( tRsp, sStatus, tJqXhr )
  {
    if ( g_iPair < g_iValueUnitPairs )
    {
      g_iPair ++;
      rq();
    }
    else
    {
      clearWaitCursor();

      // Initialize fields to previous values
      var sValue = g_sPrevValue;
      var sUnits = g_sPrevUnits;
      var sTime = g_sPrevTime;

      // If request succeeded, extract new values
      var tBnRsp = tRsp.bacnet_response;
      if ( tBnRsp.success )
      {
        var tData = tBnRsp.data;
        if ( tData.success )
        {
          sValue = Math.round( tData.presentValue );
          sUnits = tData.units;
          var tDate = new Date;
          sTime = tDate.toLocaleString();
        }
      }

      // Update table cells
      $( '#value_' + g_iInstance + '_' + g_iPair ).html( sValue );
      $( '#units_' + g_iInstance + '_' + g_iPair ).html( sUnits );
      $( '#time_' + g_iInstance ).html( sTime );

      // Increment instance index
      if ( g_iInstance < ( g_aInstances.length - 1 ) )
      {
        g_iInstance ++;
      }
      else
      {
        g_iInstance = 0;
        g_iTimeoutMs = 5000;
      }

      // Trigger next request sequence
      setTimeout( rq, g_iTimeoutMs );
    }
  }

  function readFail( tJqXhr, sStatus, sErrorThrown )
  {
    clearWaitCursor();
    $( '#response' ).append( '<tr><td>error</td></tr>' );

    console.log( "=> ERROR=" + sStatus + " " + sErrorThrown );
    console.log( "=> HEADER=" + JSON.stringify( tJqXhr ) );
  }

  function setWaitCursor()
  {
    $( '#view' ).css( 'cursor', 'wait' );
    $( '#spinner' ).css( 'display', 'block' );
  }

  function clearWaitCursor()
  {
    $( '#view' ).css( 'cursor', 'default' );
    $( '#spinner' ).css( 'display', 'none' );
  }
</script>

<div class="container">

  <div>
    <table class="table">

      <thead>
        <tr>
          <th>
            <?=$sFirstColName?>
          </th>

          <?php
            foreach ( $aInstanceColNames as $tColNames )
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

      <tbody id="bgt_table_body" >
      </tbody>

    </table>
  </div>

  <div id="spinner" class="spinner" >
  </div>
</div>
