<?php
  // Copyright 2018 BACnet Gateway.  All rights reserved.

  include $_SERVER['DOCUMENT_ROOT'] . '/util/tablesorter.php';

  // Get list of facilities from agents file
  $file = fopen( $_SERVER["DOCUMENT_ROOT"]."/../bg/agents.csv", 'r' );

  $aFacilities = [];
  fgetcsv( $file );
  while( ! feof( $file ) )
  {
    $aLine = fgetcsv( $file );
    $sFacility = trim( $aLine[0] );
    if ( $sFacility && substr( $sFacility, 0, 1 ) != '#' )
    {
      $aFacilities[$sFacility] = [];
    }
  }

  fclose( $file );

  // Get instance IDs, organized by facility and location
  $file = fopen( $_SERVER["DOCUMENT_ROOT"]."/advanced/instances.csv", 'r' );

  fgetcsv( $file );
  while( ! feof( $file ) )
  {
    $aLine = fgetcsv( $file );
    $sFacility = trim( $aLine[0] );
    if ( $sFacility && substr( $sFacility, 0, 1 ) != '#' )
    {
      $sLocation = trim( $aLine[1] );
      $aFacilities[$sFacility][$sLocation] = [ 'instance' => trim( $aLine[2] ), 'metric' => trim( $aLine[3] ) ];
    }
  }

  fclose( $file );

  $sFacilities = json_encode( $aFacilities );
?>

<script>

  var tFacilities = JSON.parse( '<?=$sFacilities?>' );

  var aTypes =
        { 'analogInput':0
        , 'analogOutput':1
        , 'analogValue':2
        , 'binaryInput':3
        , 'binaryOutput':4
        , 'binaryValue':5
        , 'calendar':6
        , 'command':7
        , 'device':8
        , 'eventEnrollment':9
        , 'file':10
        , 'group':11
        , 'loop':12
        , 'multiStateInput':13
        , 'multiStateOutput':14
        , 'notificationClass':15
        , 'program':16
        , 'schedule':17
        , 'averaging':18
        , 'multiStateValue':19
        , 'trendLog':20
        , 'lifeSafetyPoint':21
        , 'lifeSafetyZone':22
        , 'accumulator':23
        , 'pulseConverter':24
        , 'eventLog':25
        , 'globalGroup':26
        , 'trendLogMultiple':27
        , 'loadControl':28
        , 'structuredView':29
        , 'accessDoor':30
        , 'accessCredential':32
        , 'accessPoint':33
        , 'accessRights':34
        , 'accessUser':35
        , 'accessZone':36
        , 'credentialDataInput':37
        , 'networkSecurity':38
        , 'bitstringValue':39
        , 'characterstringValue':40
        , 'datePatternValue':41
        , 'dateValue':42
        , 'datetimePatternValue':43
        , 'datetimeValue':44
        , 'integerValue':45
        , 'largeAnalogValue':46
        , 'octetstringValue':47
        , 'positiveIntegerValue':48
        , 'timePatternValue':49
        , 'timeValue':50
        , 'notificationForwarder':51
        , 'alertEnrollment':52
        , 'channel':53
        , 'lightingOutput':54
        };

  $( document ).ready( init );

  function init()
  {
    $( '#facility' ).change( loadLocation );
    $( '#location' ).change( loadInstance );

    loadLocation();

    for ( sType in aTypes )
    {
      var sOption = '<option>' + sType + '</option>';
      $( '#type' ).append( sOption );
    }

    $( '#type' ).val( 'analogInput' );

    // Initialize the tablesorter
    $( '#advanced_table' ).tablesorter( g_tTableProps );
  }

  function loadLocation()
  {
    var sFacility = $( '#facility' ).val();
    var aLocations = tFacilities[sFacility];

    // Format location dropdown
    var sHtml = '';
    for ( var sLocation in aLocations )
    {
      console.log( sLocation );
      var tObject = aLocations[sLocation];
      console.log( tObject );
      sHtml += '<option value="' + tObject.instance + '">' + sLocation + ' ' + tObject.metric + '</option>';
    }
    $( '#location' ).html( sHtml );

    // Load corresponding instance
    loadInstance();
  }

  function loadInstance()
  {
    $( '#instance' ).val( $( '#location' ).val() );
  }

  function rq()
  {
    setWaitCursor();

    var sArgList =
        '?facility=' + $( '#facility' ).val()
      + '&instance=' + $( '#instance' ).val()
      + '&type=' + $( '#type' ).val();

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
    var tDate = new Date;
    var sTime = tDate.toLocaleString();
    var sStatus = 'OK';
    var sClass = '';

    var tBnRsp = tRsp.bacnet_response;

    if ( tBnRsp.success )
    {
      var tData = tBnRsp.data;

      if ( tData.success )
      {
        sClass = 'bg-dropbox';
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
    var sHtml = '<tr class="' + sClass + '">';
    sHtml += '<td>' + $( '#facility' ).val() + '</td>';
    sHtml += '<td>' + $( '#instance' ).val() + '</td>';
    sHtml += '<td>' + $( '#type' ).val() + '</td>';
    sHtml += '<td>' + sValue + '</td>';
    sHtml += '<td>' + sUnits + '</td>';
    sHtml += '<td>' + sTime + '</td>';
    sHtml += '<td>' + sStatus + '</td>';
    sHtml += '</tr>';
    $( '#responses' ).prepend( sHtml );

    // Update tablesorter cache
    var tTable = $( '#advanced_table' )
    tTable.on( 'tablesorter-ready', function(){ $('#advanced_table').off( 'tablesorter-ready' ); } );
    tTable.trigger( 'update' );
  }

  function rqFail( tJqXhr, sStatus, sErrorThrown )
  {
    clearWaitCursor();
    console.log( "=> ERROR=" + sStatus + " " + sErrorThrown );
    console.log( "=> HEADER=" + JSON.stringify( tJqXhr ) );
  }

  function setWaitCursor()
  {
    $( '#submit_button' ).prop( 'disabled', true );
    $( '#view' ).css( 'cursor', 'wait' );
  }

  function clearWaitCursor()
  {
    $( '#responses .bg-dropbox' ).removeClass( 'bg-dropbox' );
    $( '#view' ).css( 'cursor', 'default' );
    $( '#submit_button' ).prop( 'disabled', false );
  }
</script>

<div class="container">
  <div>
    <form action="javascript:rq();">

      <div class="form-group">
        <label for="facility">Facility</label>
        <select id="facility" class="form-control" >
          <?php
            foreach ( $aFacilities as $sFacility => $aNotUsed )
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
        <label for="location">Location</label>
        <select id="location" class="form-control" >
        </select>
      </div>

      <div class="form-group">
        <label for="instance">Instance</label>
        <input type="text" class="form-control" id="instance" placeholder="Instance" required>
      </div>

      <div class="form-group">
        <label for="type">Type</label>
        <select id="type" class="form-control" >
        </select>
      </div>

      <button id="submit_button" type="submit" class="btn btn-default" title="Get value and units for specified Facility and Instance" >Get value and units</button>
    </form>
  </div>

  <br/>
  <br/>

  <table id="advanced_table" class="table">
    <thead>
      <tr>
        <th>
          Facility
        </th>
        <th>
          Instance
        </th>
        <th>
          Type
        </th>
        <th>
          Value
        </th>
        <th>
          Units
        </th>
        <th>
          Time
        </th>
        <th>
          Status
        </th>
      <tr>
    <thead>
    <tbody id="responses" >
    </tbody>
  </table>

</div>
