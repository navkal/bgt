<?php
  // Copyright 2018 BACnet Gateway.  All rights reserved.

  // Read CSV file containing list of electrical meters
  $file = fopen( 'test_electricity.csv', 'r' );
  fgetcsv( $file );

  // Save CSV data in array
  $aMeters = [];
  while( ! feof( $file ) )
  {
    $aMeter = fgetcsv( $file );
    if ( is_array( $aMeter ) && ( count( $aMeter ) > 1 ) )
    {
      array_push( $aMeters, $aMeter );
    }
  }

  // Convert to JSON
  $sMeters = json_encode( $aMeters );

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

  var g_aMeters = null;
  var g_iMeter = 0;

  $( document ).ready( onDocumentReady );

  function onDocumentReady()
  {
    clearWaitCursor();

    // Load list of meters
    g_aMeters = JSON.parse( '<?=$sMeters?>' );

    // Initialize table
    var sHtml = '';
    for ( var iMeter in g_aMeters )
    {
      sHtml += '<tr>';
      sHtml += '<td>' + g_aMeters[iMeter][0] + '</td>';
      sHtml += '<td id="value_' + iMeter + '" style="text-align:right" ></td>';
      sHtml += '<td id="units_' + iMeter + '"></td>';
      sHtml += '<td id="time_' + iMeter + '"></td>';
      sHtml += '</tr>';
    }

    $( '#meter_table_body' ).html( sHtml );

    // Issue first request
    rq();
  }

  function rq()
  {
    setWaitCursor();

    $( '#value_' + g_iMeter ).html( '-' );
    $( '#units_' + g_iMeter ).html( '-' );
    $( '#time_' + g_iMeter ).html( '-' );

    var sArgList =
        '?facility=ahs'
      + '&instance=' + g_aMeters[g_iMeter][1];

    // Issue request to BACnet Gateway
    $.ajax(
      'http://localhost:8000/bg.php/' + sArgList,
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
    clearWaitCursor();

    // Initialize fields
    var sValue = '(error)';
    var sUnits = '(error)';

    // Extract values from response
    var tBnRsp = tRsp.bacnet_response;
    if ( tBnRsp.success )
    {
      var tData = tBnRsp.data;
      if ( tData.success )
      {
        sValue = Math.round( tData.presentValue );
        sUnits = tData.units;
      }
    }

    // Update table cells
    var tDate = new Date;
    $( '#value_' + g_iMeter ).html( sValue );
    $( '#units_' + g_iMeter ).html( sUnits );
    $( '#time_' + g_iMeter ).html( tDate.toLocaleString() );

    // Increment meter index
    g_iMeter = ( g_iMeter == ( g_aMeters.length - 1 ) ) ? 0 : g_iMeter + 1;

    // Trigger next request
    setTimeout( rq, 5000 );
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
    <table id="meter_table" class="table">

      <thead>
        <tr>
          <th>
            Feeder
          </th>
          <th style="text-align:right">
            Meter Value
          </th>
          <th>
            Meter Units
          </th>
          <th>
            Update Time
          </th>
        </tr>
      </thead>

      <tbody id="meter_table_body" >
      </tbody>

    </table>
  </div>

  <div id="spinner" class="spinner" >
  </div>
</div>
