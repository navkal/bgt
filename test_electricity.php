<?php
  // Copyright 2018 BACnet Gateway.  All rights reserved.

  // Read CSV file containing list of electrical meters
  $file = fopen( 'test_electricity.csv', 'r' );
  fgetcsv( $file );

  $aMeters = [];
  while( ! feof( $file ) )
  {
    $aMeter = fgetcsv( $file );
    if ( is_array( $aMeter ) && ( count( $aMeter ) > 1 ) )
    {
      array_push( $aMeters, $aMeter );
    }
  }

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

    g_aMeters = JSON.parse( '<?=$sMeters?>' );

    var sHtml = '';
    for ( var iMeter in g_aMeters )
    {
      sHtml += '<tr>';
      sHtml += '<td>' + g_aMeters[iMeter][0] + '</td>';
      sHtml += '<td id="value_' + iMeter + '">(n/a)</td>';
      sHtml += '<td id="units_' + iMeter + '">(n/a)</td>';
      sHtml += '</tr>';
    }

    $( '#meter_table_body' ).html( sHtml );

    rq();
  }

  function rq()
  {
    setWaitCursor();

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
    console.log( tRsp );
    clearWaitCursor();

    $( '#value_' + g_iMeter ).html( g_iMeter );
    $( '#units_' + g_iMeter ).html( g_iMeter );

    g_iMeter = ( g_iMeter == ( g_aMeters.length - 1 ) ) ? 0 : g_iMeter + 1;

    setTimeout( rq, 3000 );
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
          <th>
            Meter Reading
          </th>
          <th>
            Units
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
