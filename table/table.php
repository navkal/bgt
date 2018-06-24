<?php
  // Copyright 2018 BACnet Gateway.  All rights reserved.

  include $_SERVER['DOCUMENT_ROOT'] . '/util/tablesorter.php';

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
      // Strip out single and double quotes
      $aLine[0] = str_replace( "'", '', $aLine[0] );
      $aLine[0] = str_replace( '"', '', $aLine[0] );

      // Save the line
      array_push( $aLines, $aLine );
    }
  }
  fclose( $file );

  // Sort and convert to JSON
  usort( $aLines, "compareLines" );
  $sLines = json_encode( $aLines );


  function compareLines( $aLine1, $aLine2 )
  {
    error_log( '======>' . $aLine1[0] . ' ' . $aLine2[0] . ' ? ' . strcmp( $aLine1[0], $aLine2[0] ) );
    return strcmp( $aLine1[0], $aLine2[0] );
  }
?>


<script>

  var g_aRows = null;
  var g_iInstanceOffset = 0;
  var g_iRow = 0;
  var g_iTimeoutMs = 0;
  var g_aRowData = [];

  var g_sSuccessClass = 'bg-row-success';
  var g_sPendingClass = 'bg-row-pending';

  $( document ).ready( onDocumentReady );

  function onDocumentReady()
  {
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

    $( '#bgt_table > tbody' ).html( sHtml );

    // Initialize the tablesorter
    $( '#bgt_table' ).tablesorter( g_tTableProps );

    // Issue first request
    g_iInstanceOffset = 2;
    rq();
  }

  function rq()
  {
    setWaitCursor();

    // Highlight current row as pending
    $( '#row_' + g_iRow + ' > td' ).addClass( g_sPendingClass );

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
      g_aRowData.push( tBnRsp.data );

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
        for ( var iData in g_aRowData )
        {
          var tData = g_aRowData[iData];
          $( '#value_' + g_iRow + '_' + iPair ).html( ( tData.presentValue == '' ) ? '' : Math.round( tData.presentValue ) );
          $( '#units_' + g_iRow + '_' + iPair ).html( tData.units );
          iPair ++;
        }

        // Update date
        var tDate = new Date;
        sTime = tDate.toLocaleString();
        $( '#time_' + g_iRow ).html( sTime );

        // Advance to next row
        nextRow( true );
      }
    }
  }

  // Advance to next row
  function nextRow( bSuccess )
  {
    // Clear highlighting
    $( '#bgt_table > tbody .' + g_sPendingClass ).removeClass( g_sPendingClass );
    $( '#bgt_table > tbody .' + g_sSuccessClass ).removeClass( g_sSuccessClass );

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
    g_aRowData = [];

    // Update tablesorter cache and trigger next request sequence
    var tTable = $( '#bgt_table' )
    tTable.on( 'tablesorter-ready', onTablesorterReady );
    tTable.trigger( 'update' );
  }

  function onTablesorterReady()
  {
    $('#bgt_table').off( 'tablesorter-ready' );
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
  }

  function clearWaitCursor()
  {
    $( '#view' ).css( 'cursor', 'default' );
  }
</script>

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
