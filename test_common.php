<?php
  // Copyright 2018 BACnet Gateway.  All rights reserved.

  // Read CSV file describing data to be retrieved and presented
  $file = fopen( $sCsvFilename, 'r' );
  fgetcsv( $file );

  // Save CSV data in array
  $aLines = [];
  while( ! feof( $file ) )
  {
    $aLine = fgetcsv( $file );
    if ( is_array( $aLine ) && ( count( $aLine ) > 1 ) && ( $aLine[0][0] != '#' ) )
    {
      array_push( $aLines, $aLine );
    }
  }

  // Convert to JSON
  $sLines = json_encode( $aLines );

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

  var g_aRows = null;
  var g_iInstanceOffset = 0;
  var g_iRow = 0;
  var g_iTimeoutMs = 0;
  var g_aData = [];

  var g_sSuccessClass = 'bg-success';
  var g_sPendingClass = 'text-muted';

  $( document ).ready( onDocumentReady );

  function onDocumentReady()
  {
    clearWaitCursor();

    // Load list of rows
    g_aRows = JSON.parse( '<?=$sLines?>' );

    // Initialize table
    var sHtml = '';
    for ( var iRow in g_aRows )
    {
      // Create row
      sHtml += '<tr id="row_' + iRow + '">';

      // Create cell for label in first column
      sHtml += '<td>' + g_aRows[iRow][0] + '</td>';

      // Create cells for value-unit pairs
      for ( var iPair = 2; iPair < g_aRows[iRow].length; iPair ++ )
      {
        sHtml += '<td id="value_' + iRow + '_' + iPair + '" style="text-align:right" ></td>';
        sHtml += '<td id="units_' + iRow + '_' + iPair + '"></td>';
      }

      // Create cell for time
      sHtml += '<td id="time_' + iRow + '"></td>';
      sHtml += '</tr>';
    }

    $( '#bgt_table_body' ).html( sHtml );

    // Issue first request
    g_iInstanceOffset = 2;
    rq();
  }

  function rq()
  {
    setWaitCursor();

    // Highlight current row as pending
    $( '#row_' + g_iRow ).addClass( g_sPendingClass );

    // Get the next instance
    var sInstance = g_aRows[g_iRow][g_iInstanceOffset];

    if ( sInstance )
    {
      // Got an instance

      var sArgList =
          '?facility=' + g_aRows[g_iRow][1]
        + '&instance=' + sInstance;

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
    else
    {
      // Instance was empty

      // Construct empty response
      var tRsp =
      {
        bacnet_response:
        {
          success: true,
          data:
          {
            success: true,
            requested_property: 'presentValue',
            presentValue: '',
            units: ''
          }
        }
      };

      // Invoke completion handler
      rqDone( tRsp );
    }
  }

  function rqDone( tRsp, sStatus, tJqXhr )
  {
    clearWaitCursor();

    var tBnRsp = tRsp.bacnet_response;
    if ( ! tBnRsp.success || ! tBnRsp.data.success )
    {
      // Request failed; advance to next row
      nextRow( false );
    }
    else
    {
      // Request succeeded

      // Save data
      g_aData.push( tBnRsp.data );

      if ( g_iInstanceOffset < g_aRows[g_iRow].length - 1 )
      {
        // Continue current sequence of requests

        // Increment pair index
        g_iInstanceOffset ++;

        // Request the next pair
        rq();
      }
      else
      {
        // Handle completion of request sequence for current row

        // Update pairs
        var iPair = 2;
        for ( var iData in g_aData )
        {
          var tData = g_aData[iData];
          $( '#value_' + g_iRow + '_' + iPair ).html( ( tData.presentValue == '' ) ? '' : Math.round( tData.presentValue ) );
          $( '#units_' + g_iRow + '_' + iPair ).html( tData.units );
          iPair ++;
        }

        // Update date
        var tDate = new Date;
        sTime = tDate.toLocaleString();
        $( '#time_' + g_iRow ).html( sTime );

        nextRow( true );
      }
    }
  }

  // Advance to next row
  function nextRow( bSuccess )
  {
    // Clear highlighting
    $( '#bgt_table_body .' + g_sPendingClass ).removeClass( g_sPendingClass );
    $( '#bgt_table_body .' + g_sSuccessClass ).removeClass( g_sSuccessClass );

    // Optionally highlight current row
    if ( bSuccess )
    {
      $( '#row_' + g_iRow ).addClass( g_sSuccessClass );
    }

    // Advance row index
    if ( g_iRow < ( g_aRows.length - 1 ) )
    {
      g_iRow ++;
    }
    else
    {
      g_iRow = 0;
      g_iTimeoutMs = 5000;
    }

    // Reinitialize variables
    g_iInstanceOffset = 2;
    g_aData = [];

    // Trigger next request sequence
    setTimeout( rq, g_iTimeoutMs );
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
    <table class="table">

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

      <tbody id="bgt_table_body" >
      </tbody>

    </table>
  </div>

  <div id="spinner" class="spinner" >
  </div>
</div>
