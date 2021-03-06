<?php
  // Copyright 2018 Building Energy Monitor.  All rights reserved.

  include $_SERVER['DOCUMENT_ROOT'] . '/util/tablesorter.php';

  // Get facilities map from gateway
  $curl = curl_init();
  curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
  curl_setopt( $curl, CURLOPT_URL, 'http://' . $_SESSION['bgt']['host'] . ':' . $_SESSION['bgt']['port'] . '/?facilities' );
  $aFacMap = json_decode( json_encode( json_decode( curl_exec( $curl ) ) ), true );

  // Initialize list of facilities with mapping to facility type and locations
  $aFacilities = [];
  foreach ( $aFacMap as $sFacName => $aFacType )
  {
    $aFacilities[$sFacName] = [ 'facility_type' => $aFacType['facility_type'], 'locations' => [] ];
  }

  // Load instance information into facilities structure
  $file = fopen( $_SERVER["DOCUMENT_ROOT"]."/advanced/advanced.csv", 'r' );

  fgetcsv( $file );
  while( ! feof( $file ) )
  {
    $aLine = fgetcsv( $file );
    $sFacility = trim( $aLine[0] );
    if ( ( $sFacility != '' ) && ( substr( $sFacility, 0, 1 ) != '#' ) )
    {
      $sLocation = trim( $aLine[1] );
      $sInstance = trim( $aLine[3] );
      if ( ( $sLocation != '' ) && ( $sInstance != '' ) )
      {
        $sMetric = trim( $aLine[2] );
        $sType = trim( $aLine[4] );
        $sLocation .= ( empty( $sMetric ) ? '' : ' - ' . $sMetric );
        $aFacilities[$sFacility]['locations'][$sLocation] = [ 'location' => $sLocation, 'instance' => $sInstance, 'type' => $sType ];
      }
    }
  }

  fclose( $file );

  // Sort
  ksort( $aFacilities );
  foreach ( $aFacilities as $sFacility => $tNotUsed )
  {
    ksort( $aFacilities[$sFacility]['locations'] );
  }

  // Save as JSON
  $sFacilities = json_encode( $aFacilities );
?>

<script>

  var g_tFacilities = JSON.parse( '<?=$sFacilities?>' );

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
    // Set event handlers
    $( '#facility' ).on( 'change', loadLocation );
    $( '#location' ).on( 'change', updateDependentFields );
    $( '#instance' ).on( 'input', updateLocation );

    // Load the Type dropdown
    $( '#type' ).append( '<option></option>' );
    for ( sType in aTypes )
    {
      var sOption = '<option>' + sType + '</option>';
      $( '#type' ).append( sOption );
    }

    // Load the Location dropdown
    loadLocation();

    // Initialize the tablesorter
    $( '#advanced_table' ).tablesorter( g_tTableProps );
  }

  function loadLocation()
  {
    var sFacility = $( '#facility' ).val();
    var tLocations = g_tFacilities[sFacility].locations;

    // Format location dropdown
    var sHtml = '';
    for ( var sLocation in tLocations )
    {
      sHtml += '<option type="' + tLocations[sLocation].type + '" value="' + tLocations[sLocation].instance + '">' + sLocation + '</option>';
    }
    $( '#location' ).html( sHtml );

    // Load dependent fields
    updateDependentFields();
  }

  function updateDependentFields()
  {
    $( '#instance' ).val( $( '#location' ).val() );
    var sType = $( '#location option:selected' ).attr( 'type' );
    $( '#type' ).val( sType );

    // Disable Type field if the current facility does not use it
    var sFacility = $( '#facility' ).val();
    var sFacilityType = g_tFacilities[sFacility].facility_type;
    $( '#type' ).attr( 'disabled', ( sFacilityType == 'weatherStation' ) );
  }

  function updateLocation()
  {
    $( '#location' ).val( $( '#instance' ).val() );
  }

  function rq()
  {
    setWaitCursor();

    $( '#instance' ).val( $( '#instance' ).val().trim() );

    var sArgList =
        '?facility=' + $( '#facility' ).val()
      + '&instance=' + $( '#instance' ).val()
      + '&type=' + $( '#type' ).val()
      + '&live';

    // Issue request to Building Energy Gateway
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
    clearWaitCursor();

    // Extract results from response
    var sValue = '';
    var sUnits = '';
    var tDate = new Date;
    var sTime = tDate.toLocaleString();
    var sStatus = 'OK';
    var sClass = '';

    var tInstanceRsp = tRsp.instance_response;

    if ( tInstanceRsp.success )
    {
      var tData = tInstanceRsp.data;

      if ( tData.success )
      {
        sClass = 'bg-row-success';
        sValue = formatValue( tData[tData.property] );
        sUnits = tData.units;
      }
      else
      {
        sStatus = '<span class="text-muted"><small>' + tData.message + '</small></span>';
      }
    }
    else
    {
      sStatus = '<span class="text-muted"><small>' + tInstanceRsp.message + '</small></span>';
    }

    // Display results as new row in response table
    var sHtml = '<tr class="' + sClass + '">';
    sHtml += '<td>' + $( '#facility' ).val() + '</td>';
    sHtml += '<td>' + $( '#location option:selected' ).text() + '</td>';
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
    $( '#responses .bg-row-success' ).removeClass( 'bg-row-success' );
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

      <button id="submit_button" type="submit" class="btn btn-primary mt-1" title="Get value and units for specified Facility and Instance" >Get value</button>
    </form>
  </div>

  <br/>

  <table id="advanced_table" class="table">
    <thead>
      <tr>
        <th>
          Facility
        </th>
        <th>
          Location
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

<script src="/util/util.js?version=<?=time()?>"></script>
