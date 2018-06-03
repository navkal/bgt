<?php
  // Copyright 2018 BACnet Gateway.  All rights reserved.

  // Get list of facilities from agents file
  $file = fopen( $_SERVER["DOCUMENT_ROOT"]."/../bg/agents.csv", 'r' );

  $aFacilities = [];
  fgetcsv( $file );
  while( ! feof( $file ) )
  {
    $aLine = fgetcsv( $file );
    if ( $aLine[0] )
    {
      array_push( $aFacilities, $aLine[0] );
    }
  }

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

  $( document ).ready( clearWaitCursor );

  function rq()
  {
    setWaitCursor();

    var sArgList =
        '?facility=' + $( '#facility' ).val()
      + '&instance=' + $( '#instance' ).val();

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
    .done( rqDone )
    .fail( rqFail );
  }

  function rqDone( tRsp, sStatus, tJqXhr )
  {
    console.log( tRsp );
    clearWaitCursor();

    // Extract results from response
    var sValue = '';
    var sUnits = '';
    var sStatus = 'OK';

    var tBnRsp = tRsp.bacnet_response;

    if ( tBnRsp.success )
    {
      var tData = tBnRsp.data;

      if ( tData.success )
      {
        sValue = Math.round( tData[tData.requested_property] );
        sUnits = tData.units;
      }
      else
      {
        sStatus = '<span class="text-muted"><small>' + tData.message + '</small></span>';
      }
    }
    else
    {
      sStatus = '<span class="text-muted"><small>' + tBnRsp.message + '</small></span>';
    }

    // Display results as new row in response table
    var sHtml = '<tr>';
    sHtml += '<td>' + $( '#facility' ).val() + '</td>';
    sHtml += '<td>' + $( '#instance' ).val() + '</td>';
    sHtml += '<td>' + sValue + '</td>';
    sHtml += '<td>' + sUnits + '</td>';
    sHtml += '<td>' + sStatus + '</td>';
    sHtml += '</tr>';
    $( '#responses' ).append( sHtml );
  }

  function rqFail( tJqXhr, sStatus, sErrorThrown )
  {
    clearWaitCursor();
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
    <form action="javascript:rq();">

      <div class="form-group">
        <label for="facility">Facility</label>
        <select id="facility" class="form-control" >
          <?php
            foreach ( $aFacilities as $sFacility )
            {
          ?>
              <option>
                <?=$sFacility?>
              </option>
          <?php
            }
          ?>
        </select>
      </div>

      <div class="form-group">
        <label for="instance">Instance</label>
        <input type="text" class="form-control" id="instance" placeholder="Instance" required>
      </div>

      <button type="submit" class="btn btn-default" title="Get value and units for specified Facility and Instance" >Get value and units</button>
    </form>
  </div>

  <br/>
  <br/>

  <table class="table">
    <thead>
      <tr>
        <th>
          Facility
        </th>
        <th>
          Instance
        </th>
        <th>
          Value
        </th>
        <th>
          Units
        </th>
        <th>
          Status
        </th>
      <tr>
    <thead>
    <tbody id="responses" >
    </tbody>
  </table>


  <div id="spinner" class="spinner" >
  </div>
</div>
