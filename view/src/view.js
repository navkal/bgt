// Copyright 2018 Building Energy Monitor.  All rights reserved.

if ( ! Array.prototype.fill )
{
  Array.prototype.fill = function( value )
  {
    var aFill = [];

    for ( var i = 0; i < this.length; i++ )
    {
      aFill[i] = value;
    }

    return aFill;
  };
}

var g_iStartTime = Date.now();

var g_tTable = null;
var g_iInstanceOffset = 0;
var g_iRow = 0;
var g_aRowData = [];
var g_tGraphData = {};
var g_bHorizontal = null;

var g_sSuccessClass = 'bg-row-success';
var g_sPendingClass = 'bg-row-pending';

var VERTICAL_MAX = 10;
var NARROW_MAX = 768;
var SPLIT_MODE_NARROW = 'narrow';
var SPLIT_MODE_WIDE = 'wide';
var g_sSplitMode = SPLIT_MODE_WIDE;
var g_tWideTableParent = null;
var g_tNarrowTableParent = null;
var g_tGraphSplit = null;

var g_tViewTableProps = jQuery.extend( true, { sortList: [[0,0]] }, g_tTableProps );
var g_iTablesorterThemeTopShift = 0;
var g_tFirstColParser =
{
  id: 'firstcol',
  is: function(){return false;},
  format: function(s){return s;},
  parsed: false,
  type: 'text'
};

var g_tDateFormatOptions = { weekday: 'short', year: 'numeric', month: 'numeric', day: 'numeric' };

var g_bRefreshing = false;


$( document ).ready( onDocumentReady );

function onDocumentReady()
{
  // Initialize table
  initTable();

  // Initialize graphs
  initGraphs();

  // Initialize graph options dialog
  initGraphOptionsDialog();

  // Initialize layout framework
  switch( g_sLayoutMode )
  {
    case LAYOUT_MODE_TAB:
    default:
      // Initialize tab layout
      initTabs();
      break;

    case LAYOUT_MODE_SPLIT:
      // Initialize split layout
      initSplits();
      break;
  }

  // Issue first request
  g_iInstanceOffset = 2;
  rq();
}

function initTable()
{
  var sHtml = '';
  for ( var iRow in g_aRows )
  {
    var aRow = g_aRows[iRow];

    // Open row
    sHtml += '<tr id="row_' + iRow + '">';

    // Create cell for label in first column
    sHtml += '<td class="row-label" >' + aRow[0] + '</td>';

    // Create cells for value-unit pairs
    var sFacility = aRow[1];
    var aCachedTimestamps = [];
    for ( var iPair = 2; iPair < aRow.length; iPair ++ )
    {
      var sInstance = aRow[iPair];
      var sCachedValue = '';
      var sCachedUnits = '';
      if ( ( sFacility in g_tCachedValues ) && ( sInstance in g_tCachedValues[sFacility] ) )
      {
        sCachedValue = formatValue( g_tCachedValues[sFacility][sInstance].presentValue );
        sCachedUnits = g_tCachedValues[sFacility][sInstance].units;
        aCachedTimestamps.push( g_tCachedValues[sFacility][sInstance].timestamp );
      }

      sHtml += '<td id="value_' + iRow + '_' + iPair + '" style="text-align:right" >';
      sHtml += sCachedValue;
      sHtml += '</td>';
      sHtml += '<td id="units_' + iRow + '_' + iPair + '">';
      sHtml += sCachedUnits;
      sHtml += '</td>';
    }

    // Create cell for time
    sHtml += '<td id="time_' + iRow + '">';
    if ( aCachedTimestamps.length )
    {
      var tDate = new Date( Math.max( ...aCachedTimestamps ) );
      sHtml += tDate.toLocaleString();
    }
    sHtml += '</td>';

    // Close row
    sHtml += '</tr>';
  }

  // Set up the table
  $( '#bgt_table > tbody' ).html( sHtml );
  g_tTable = $( '#bgt_table' );
  g_tTable.show();

  // Add first column parser (work around tablesorter bug?)
  $.tablesorter.addParser( g_tFirstColParser );

  if ( g_sLayoutMode == LAYOUT_MODE_SPLIT )
  {
    // Set event handlers
    g_tTablePane = g_tTable.parent();
    g_tTablePane.on( 'resize', onResizeTablePane );
    g_tTablePane.on( 'scroll', onScrollTablePane );
    $( window ).on( 'scroll', onScrollWindow );

    // Initialize tablesorter with sticky header
    g_tViewTableProps.widgetOptions.stickyHeaders_offset = g_tTable.offset().top;
    var iTopBf = g_tTable.offset().top;
    g_tTable.tablesorter( g_tViewTableProps );
    g_iTablesorterThemeTopShift = g_tTable.offset().top - iTopBf;
    g_tTable.css( { marginTop: '-=' + g_iTablesorterThemeTopShift + 'px' } );
  }
  else
  {
    // Initialize tablesorter without the sticky header
    g_tViewTableProps.widgets.splice( g_tViewTableProps.widgets.indexOf( 'stickyHeaders' ), 1 );
    g_tTable.tablesorter( g_tViewTableProps );
  }
}

function initGraphs()
{
  g_bHorizontal = ( g_sLayoutMode == LAYOUT_MODE_TAB ) ? ( g_aRows.length > VERTICAL_MAX ) : false;

  if ( g_bFlot )
  {
    var sTickStyle =
      g_bHorizontal ?
        '<style>' +
          '.flot-y-axis .flot-tick-label' +
          '{' +
            'line-height: 1;' +
            'max-width: 70px;' +
          '}' +
        '</style>'
      :
        '<style>' +
          '.flot-x-axis .flot-tick-label' +
          '{' +
            'font-size: 12.5px;' +
            'line-height: 1;' +
            'padding: 20px;' +
            'transform: rotate(-45deg);' +
            '-ms-transform: rotate(-45deg);' +
            '-moz-transform: rotate(-45deg);' +
            '-webkit-transform: rotate(-45deg);' +
            '-o-transform: rotate(-45deg);' +
          '}'
        '</style>';

    $( 'head' ).append( sTickStyle );
  }
}

function initGraphOptionsDialog()
{
  // Set handler for dialog show event
  $( '#graphOptionsDialog' ).on( 'show.bs.modal', onShowGraphOptionsDialog );
}

function initTabs()
{
  if ( g_aGraphSelectors.length )
  {
    // Set handler to update graphs when graph tab is selected
    $( 'a.graph-tab' ).on( 'shown.tab.bs', onGraphTabShown );
  }
  else
  {
    // Remove tab styling
    $( '#view > .container-fluid .nav.nav-tabs' ).remove();
    $( '#view > .container-fluid .tab-content' ).removeClass();
    $( '#tableTab' ).removeClass();
  }
}

function initSplits()
{
  if ( g_aGraphSelectors.length )
  {
    // Set up split styling

    $( '#view > .container-fluid' ).css( 'height', '85%' );

    Split(
      ['#wideTablePane', '#wideGraphPane'],
      {
        gutterSize: 8,
        minSize: 0,
        cursor: 'col-resize'
      }
    );
    g_tWideTableParent = $( '#wideTablePane .content' );
    g_tNarrowTableParent = $( '#narrowTablePane' );

    // Split the graph pane
    splitGraphPane();

    // Set up toggling between wide and narrow modes
    $( window ).on( 'resize', onWindowResize );
    onWindowResize();
  }
  else
  {
    // Remove split styling

    $( '#view > .container-fluid' ).prepend( '<br/>' );

    $( '#wideGraphPane' ).hide();

    $( '#wideTablePane > .split.content' )
      .removeClass( 'split' )
      .removeClass( 'content' )
      .addClass( 'container' );

    $( '#wideTablePane' )
      .removeClass( 'split' )
      .removeClass( 'split-horizontal' );

    $( '#wideTablePane' )
      .parent()
      .removeClass( 'backdrop' );
  }
}

function splitGraphPane()
{
  if ( g_aGraphSelectors.length > 1 )
  {
    var nGraphs = g_aGraphSelectors.length;
    var aSizes = Array( nGraphs ).fill( Math.floor( 100 / nGraphs ) );

    g_tGraphSplit = Split(
      g_aGraphSelectors,
      {
        direction: 'vertical',
        sizes: aSizes,
        minSize: 0,
        gutterSize: 8,
        cursor: 'row-resize'
      }
    );
  }
}

function onWindowResize()
{
  var sSplitMode = ( $( window ).width() <= NARROW_MAX ) ? SPLIT_MODE_NARROW : SPLIT_MODE_WIDE;

  if ( sSplitMode != g_sSplitMode )
  {
    if ( sSplitMode == SPLIT_MODE_NARROW )
    {
      wideToNarrow();
    }
    else
    {
      narrowToWide();
    }
  }

  g_sSplitMode = sSplitMode;

  // Handle possibility that window is initially scrolled
  onScrollWindow();
}

function wideToNarrow()
{
  // Hide the wide div
  $( '#wide' ).hide();

  // Move the table
  g_tNarrowTableParent.append( g_tTable );

  // Move the sticky header
  getStickyWrapper().find( 'thead' ).css( 'border', '2px solid red' ); // <-- debug
  g_tNarrowTableParent.append( getStickyWrapper() );

  // Clear the narrow graph pane
  $( '#narrowGraphPane' ).html( '' );

  // Move the graphs
  for ( var iGraphSel in g_aGraphSelectors )
  {
    var tGraphDiv = $( g_aGraphSelectors[iGraphSel] );

    $( '#narrowGraphPane' ).append( tGraphDiv );
    $( '#narrowGraphPane' ).append( '<hr/>' );

    tGraphDiv
      .removeClass( 'split' )
      .removeClass( 'content' );
  }

  // Set spacing around bar graphs
  $( '#narrow .bar-graph' )
    .css( 'margin-bottom', '70px' )
    .css( 'height', '100%' );

  // Show the narrow div
  $( '#narrow' ).show();
}

function narrowToWide()
{
  // Hide the narrow div
  $( '#narrow' ).hide();

  // Move the table
  g_tWideTableParent.append( g_tTable );
  g_tTable.css( 'height', '95%' );

  // Move the graphs
  for ( var iGraphSel in g_aGraphSelectors )
  {
    var tGraphDiv = $( g_aGraphSelectors[iGraphSel] );

    $( '#wideGraphPane' ).append( tGraphDiv );

    tGraphDiv
      .addClass( 'split' )
      .addClass( 'content' );
  }

  // Clear spacing around bar graphs
  $( '#wide .bar-graph' )
    .css( 'margin-bottom', '' )
    .css( 'height', '' );

  // Re-split the graph pane
  if ( g_tGraphSplit )
  {
    g_tGraphSplit.destroy();
    splitGraphPane();
  }

  // Show the wide div
  $( '#wide' ).show();
}

function onShowGraphOptionsDialog( tEvent )
{
  if ( $( tEvent.target ).attr( 'id' ) == 'graphOptionsDialog' )
  {
    var tOptionsButton = $( tEvent.relatedTarget );

    // Get graph name
    var sGraphName = tOptionsButton.data( 'graph_name' );

    // Display graph name in dialog box
    $( '#graphOptionsGraphName' ).text( sGraphName );

    // Save graph name in datepicker
    $( '#baselineDatepicker' ).attr( 'graph_name', sGraphName );

    // Set initial datepicker value
    var tCurrentDate = new Date( tOptionsButton.data( 'timestamp' ) );
    var sCurrentDate = tCurrentDate.toLocaleDateString( 'en-US', g_tDateFormatOptions ).replace( ',', '' );
    $( '#baselineDatepicker input' ).val( sCurrentDate );
    $( '#graphOptionsDialog' ).attr( 'original_date', sCurrentDate );

    // Determine lower and upper datepicker bounds
    var tStartDate = new Date( tOptionsButton.data( 'first_timestamp' ) );
    var tEndDate = new Date( tOptionsButton.data( 'last_timestamp' ) );

    // Initialize the datepicker
    $( '#baselineDatepicker' ).datepicker(
      {
        autoclose: true,
        todayHighlight: true,
        format: 'D m/d/yyyy',
        startDate: tStartDate.toLocaleDateString(),
        endDate: tEndDate.toLocaleDateString()
      }
    );

    // Initialize cost fields
    var tGraphOptions = g_tGraphOptions[sGraphName];
    if ( tGraphOptions.dollarsPerUnit )
    {
      $( '#showAsCost' ).prop( 'checked', true );
      $( '#dollarsPerUnit' ).val( tGraphOptions.dollarsPerUnit );
      if ( ! $( '#dollarsPerUnit' ).attr( 'last_value' ) )
      {
        $( '#dollarsPerUnit' ).attr( 'last_value', $( '#dollarsPerUnit' ).val() );
      }
    }
  }
}

function onChangeShowAsCost( tEvent )
{
  var bChecked = $( '#showAsCost' ).prop( 'checked' );
  $( '#dollarsPerUnit' ).prop( 'disabled', ! bChecked );
  $( '#dollarsPerUnit' ).val( bChecked ? $( '#dollarsPerUnit' ).attr( 'last_value' ) : '' );
}

function onChangeDollarsPerUnit( tEvent )
{
  // If cost value is valid, save as last value
  var nVal = Number( $( '#dollarsPerUnit' ).val() );
  var nMin = $( '#dollarsPerUnit' ).attr( 'min' );
  if ( ! isNaN( nVal ) && ( nVal >= nMin ) )
  {
    $( '#dollarsPerUnit' ).val( nVal );
    $( '#dollarsPerUnit' ).attr( 'last_value', nVal );
  }
}

function onSubmitGraphOptions( tEvent )
{
  if ( validateGraphOptions() )
  {
    // Hide the modal dialog
    $( '#graphOptionsDialog' ).modal( 'hide' );

    // Save cost configuration
    var sGraphName = $( '#baselineDatepicker' ).attr( 'graph_name' );
    g_tGraphOptions[sGraphName].dollarsPerUnit = $( '#showAsCost' ).prop( 'checked' ) ? $( '#dollarsPerUnit' ).val() : 0;

    var sDate = $( '#baselineDatepicker input' ).val();
    var sOriginalDate = $( '#graphOptionsDialog' ).attr( 'original_date' );

    if ( sDate != sOriginalDate )
    {
      // Extract the timestamp from the datepicker
      var tDate = new Date( sDate );
      var iTimestamp = tDate.getTime();

      // Set post arguments
      var tPostData = new FormData();
      tPostData.append( 'csv_basename', g_sCsvBasename );
      tPostData.append( 'graph_name', sGraphName );
      tPostData.append( 'timestamp', iTimestamp );

      // Post request to server
      $.ajax(
        '/baselines/get_baseline.php',
        {
          type: 'POST',
          processData: false,
          contentType: false,
          dataType : 'json',
          data: tPostData
        }
      )
      .done( submitGraphOptionsDone )
      .fail( handleAjaxError );
    }
    else
    {
      updateGraphs( false );
    }
  }
}

function validateGraphOptions()
{
  var bValid = true;
  $( '.has-error' ).removeClass( 'has-error' );
  return bValid;
}

function submitGraphOptionsDone( tRsp, sStatus, tJqXhr )
{
  // Save new baseline for the target graph
  var sGraphName = tRsp.graph_name;
  var iGraph = g_tGraphNameMap[sGraphName];
  var sGraphId = g_aColNames[iGraph].graph.graph_id;
  g_tBaselines[sGraphId] = tRsp;

  // Update all bars of the target graph
  var tGraphData = g_tGraphData[sGraphId];
  for ( var sRowLabel in tGraphData )
  {
    // Get data and baseline of next bar
    var tBarData = tGraphData[sRowLabel];
    if ( sRowLabel in g_tBaselines[sGraphId].values )
    {
      var tBarBaseline = g_tBaselines[sGraphId].values[sRowLabel];

      // Update baseline value in graph data structure
      if ( tBarBaseline.units != tBarData.units )
      {
        delete( tGraphData[sRowLabel] );
      }
    }
    else
    {
      delete( tGraphData[sRowLabel] );
    }
  }

  // Update graph displays
  updateGraphs( false );
}

function onGraphTabShown( tEvent )
{
  var sGraphId = $( tEvent.target ).attr( 'href' ).substring( 1 );
  var tGraphDiv = $( '#' + sGraphId + ' .bar-graph' );

  var iGraph = g_tGraphIdMap[sGraphId];
  var sGraphName = g_aColNames[iGraph].value_col_name;
  var bDelta = g_aColNames[iGraph].graph.delta;

  updateGraphDisplay( tGraphDiv, sGraphId, sGraphName, bDelta );
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

    var sFacility = g_aRows[g_iRow][1];

    if ( g_tCachedValues && ( sFacility in g_tCachedValues ) && ( sInstance in g_tCachedValues[sFacility] ) )
    {
      var tRsp =
      {
        instance_response:
        {
          success: true,
          data: g_tCachedValues[sFacility][sInstance]
        }
      };

      rqDone( tRsp );
    }
    else
    {
      var sArgList =
          '?facility=' + sFacility
        + '&instance=' + sInstance
        + '&live';

      // Issue request to Building Energy Gateway
      $.ajax(
        g_sBuildingEnergyGatewayUrl + sArgList,
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

  }
  else
  {
    // Instance was empty

    // Construct empty response
    var tRsp =
    {
      instance_response:
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

  var tInstanceRsp = tRsp.instance_response;
  if ( ! tInstanceRsp.success || ! tInstanceRsp.data.success )
  {
    // Request failed; advance to next row
    nextRow( false );
  }
  else
  {
    // Request succeeded

    // Save data
    g_aRowData.push( tInstanceRsp.data );

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

      // Update table row
      updateRow();

      // Update graphs
      updateGraphs( true );

      // Advance to next row
      nextRow( true );
    }
  }
}

function updateRow()
{
  var aCachedTimestamps = [];

  // Update pairs
  var iPair = 2;
  for ( var iData in g_aRowData )
  {
    var tData = g_aRowData[iData];
    $( '#value_' + g_iRow + '_' + iPair ).html( formatValue( tData.presentValue ) );
    $( '#units_' + g_iRow + '_' + iPair ).html( tData.units );

    if ( 'timestamp' in tData )
    {
      aCachedTimestamps.push( tData.timestamp );
    }

    iPair ++;
  }

  // Update date
  var tDate = ( aCachedTimestamps.length ) ? new Date( Math.max( ...aCachedTimestamps ) ) : new Date();
  sTime = tDate.toLocaleString();
  $( '#time_' + g_iRow ).html( sTime );
}

function formatValue( rawValue )
{
    // Decide how to display the value
    var value = null;
    if ( rawValue === '' )
    {
      value = '';
    }
    else if ( ( -1 < rawValue ) && ( rawValue < 1 ) )
    {
      value = Math.round( rawValue * 100 ) / 100;
    }
    else
    {
      value = Math.round( rawValue );
    }

    return value;
}

function updateGraphs( bUpdateData )
{
  var aGraphs = $( '.bar-graph' );

  // Iterate over all graphs
  for ( var iGraph = 0; iGraph < aGraphs.length; iGraph ++ )
  {
    // Find the graph index
    var sGraphId = $( aGraphs[iGraph] ).parent().attr( 'id' );
    var iGraph = g_tGraphIdMap[sGraphId];

    var bDelta = g_aColNames[iGraph].graph.delta;

    // Optionally update the graph data structure
    if ( bUpdateData )
    {
      updateGraphData( sGraphId, g_aRowData[iGraph], bDelta );
    }

    // Update graph display under any of the following conditions:
    // - Graph is horizontal
    // - Not traversing cached values
    // - Traversing a cached row that should appear in the initial graph
    if ( g_bHorizontal || ! g_tCachedValues || ( g_iRow <= VERTICAL_MAX ) )
    {
      // If graph is visible, update the display
      var tGraphDiv = $( '#' + sGraphId + ' .bar-graph' );
      if ( tGraphDiv.is( ':visible' ) )
      {
        var sGraphName = g_aColNames[iGraph].value_col_name;
        updateGraphDisplay( tGraphDiv, sGraphId, sGraphName, bDelta );
      }
    }
  }
}

function updateGraphData( sGraphId, tBarData, bDelta )
{
  // If data structure for target graph does not exist, create it
  if ( ! ( sGraphId in g_tGraphData ) )
  {
    g_tGraphData[sGraphId] = {};
  }

  // Update target graph data
  var tGraphData = g_tGraphData[sGraphId];
  var sRowLabel = g_aRows[g_iRow][0];

  if ( ( tBarData.presentValue == '' ) || ( bDelta && ( ! ( sRowLabel in g_tBaselines[sGraphId].values ) || ( g_tBaselines[sGraphId].values[sRowLabel].units != tBarData.units ) ) ) )
  {
    // Value is missing, or for delta graph, baseline is missing or incompatible. Remove bar from graph data structure.
    delete tGraphData[sRowLabel];
  }
  else
  {
    // Save value
    var nValue =  Math.round( tBarData.presentValue );
    tGraphData[sRowLabel] = { value: nValue, units: tBarData.units };
  }
}

function updateGraphDisplay( tGraphDiv, sGraphId, sGraphName, bDelta )
{
  if ( ( sGraphId in g_tGraphData ) && ( tGraphDiv.width() > 0 ) )
  {
    // Determine which units to show in graph
    var tGraphData = g_tGraphData[sGraphId];
    var sGraphUnits = pickGraphUnits( tGraphData );

    // Get access to graph options object
    var tGraphOptions = g_tGraphOptions[sGraphName];

    if ( g_bFlot )
    {
      // Determine which bars to show in graph
      if ( g_sLayoutMode == LAYOUT_MODE_TAB )
      {
        var aBarLabels = Object.keys( tGraphData );
        var nBars = aBarLabels.length;
      }
      else
      {
        var aRowLabels = $( 'tr:not(.filtered) .row-label' );
        var nBars = Math.min( aRowLabels.length, VERTICAL_MAX );

        var aBarLabels = [];
        for ( var iBar = 0; iBar < nBars; iBar ++ )
        {
          aBarLabels.push( $( aRowLabels[iBar] ).text() );
        }
      }

      var iOffset = g_bHorizontal ? ( nBars - 1 ) : 0;

      // Load data values and tick labels
      var aData = [];
      var aTicks = [];
      for ( var iBarLabel in aBarLabels )
      {
        var sBarLabel = aBarLabels[iBarLabel];

        if ( sBarLabel in tGraphData )
        {
          var tRow = tGraphData[sBarLabel];
          if ( tRow.units == sGraphUnits )
          {
            var nValue = bDelta ? tRow.value - g_tBaselines[sGraphId].values[sBarLabel].value : tRow.value;
            if ( g_tGraphOptions[sGraphName].dollarsPerUnit )
            {
              nValue *= g_tGraphOptions[sGraphName].dollarsPerUnit;
            }
            aData.push( g_bHorizontal ? [ nValue, iOffset ] : [ iOffset, nValue ] );
            aTicks.push( [ iOffset, sBarLabel ] );
            iOffset += g_bHorizontal ? -1 : 1;
          }
        }
      }

      // Define dataset consisting of one series
      var sSince = '';
      if ( bDelta )
      {
        tTime = new Date( g_tBaselines[sGraphId].timestamp );
        sSince =
         ( g_tGraphOptions[sGraphName].dollarsPerUnit ? ' cost' : '' ) +
          ' since ' +
          '<button ' +
            'type="button" ' +
            'class="btn btn-outline-secondary btn-xs" ' +
            'title="' + sGraphName + ' Graph Options" ' +
            'data-toggle="modal" ' +
            'data-target="#graphOptionsDialog" ' +
            'data-timestamp="' + g_tBaselines[sGraphId].timestamp + '" ' +
            'data-first_timestamp="' + g_tBaselines[sGraphId].first_timestamp + '" ' +
            'data-last_timestamp="' + g_tBaselines[sGraphId].last_timestamp + '" ' +
            'data-graph_name="' + sGraphName + '" ' +
            '>' +
              tTime.toLocaleDateString( 'en-US', g_tDateFormatOptions ).replace( ',', '' ) + ' ' +
              '<i class="fas fa-cog" style="padding-left:3px"></i> ' +
          '</button>';
      }
      var aDataset = [ { label: '&nbsp;' + sGraphName + sSince, data: aData, color: "#54b9f8" } ];

      // Define tick formatter function
      var toLocaleString = function( v, axis )
      {
        return v.toLocaleString();
      };

      // Set up graph options
      var sRangeLabel = tGraphOptions.dollarsPerUnit ? '$ (' + tGraphOptions.dollarsPerUnit + ' per unit)' : sGraphUnits;
      var tOptions =
      {
        series:
        {
          bars:
          {
            show: true
          }
        },
        bars:
        {
          align: "center",
          barWidth: 0.7,
          horizontal: g_bHorizontal
        },
        xaxis:
        {
          axisLabel: ( g_bHorizontal ? sRangeLabel : g_sFirstColName ),
          axisLabelUseCanvas: true,
          axisLabelFontSizePixels: 14,
          axisLabelFontFamily: 'Verdana, Arial',
          axisLabelPadding: 10,
          labelWidth: 100,
          ticks: ( g_bHorizontal ? null : aTicks ),
          tickFormatter: ( g_bHorizontal ? toLocaleString : null )
        },
        yaxis:
        {
          axisLabel: g_bHorizontal ? g_sFirstColName : sRangeLabel,
          axisLabelUseCanvas: true,
          axisLabelFontSizePixels: 14,
          axisLabelFontFamily: 'Verdana, Arial',
          axisLabelPadding: 20,
          ticks: g_bHorizontal ? aTicks : null,
          tickFormatter: ( g_bHorizontal ? null : toLocaleString )
        },
        legend:
        {
          noColumns: 0,
          labelBoxBorderColor: "#1fa2f9",
          position: "ne"
        },
        grid:
        {
          hoverable: true,
          borderWidth: 2
        }
      };

      // Adjust height of horizontal bar graph
      if ( g_bHorizontal )
      {
        tGraphDiv.css( 'height', ( aData.length * 40 ) + 100);
      }

      //
      // Set up handler to display tooltip
      //

      // Initialize data structure to track previous tooltip
      var tPreviousTooltip =
      {
        dataIndex: null,
        seriesLabel: null
      };

      // Define function to show tooltip
      var showTooltip = function( x, y, sColor, sContents )
      {
        $( '<div id="tooltip">' + sContents + '</div>' ).css(
          {
            position: 'absolute',
            display: 'none',
            top: g_bHorizontal ? y-16 : y-40,
            left: g_bHorizontal ? x+10 : x-30,
            border: '2px solid ' + sColor,
            padding: '3px',
            'font-size': '9px',
            'border-radius': '5px',
            'background-color': '#fff',
            'font-family': 'Verdana, Arial, Helvetica, Tahoma, sans-serif',
            opacity: 0.9
          }
        ).appendTo( 'body' ).fadeIn( 200 );
      };

      // Define function to handle mouse hover event
      var onPlotHover = function( event, pos, item )
      {
        if ( item )
        {
          // If tooltip coordinates have changed, update the tooltip
          if ( ( tPreviousTooltip.seriesLabel != item.series.label ) || ( tPreviousTooltip.dataIndex != item.dataIndex ) )
          {
            // Save new tooltip coordinates
            tPreviousTooltip.dataIndex = item.dataIndex;
            tPreviousTooltip.seriesLabel = item.series.label;

            // Clear previous tooltip
            $( '#tooltip' ).remove();

            // Set up new tooltip
            var x = item.datapoint[0];
            var y = item.datapoint[1];
            var iTick = Object.keys( g_tGraphData[sGraphId] ).length - y - 1;

            // Format tooltip text
            var sBarLabel = g_bHorizontal ? item.series.yaxis.ticks[iTick].label : item.series.xaxis.ticks[x].label;
            var sBarValue = Math.round( g_bHorizontal ? x : y ).toLocaleString();
            var sGraphUnits = g_bHorizontal ? item.series.xaxis.options.axisLabel : item.series.yaxis.options.axisLabel;
            if ( sGraphUnits.startsWith( '$' ) )
            {
              sBarValue = '$' + sBarValue;
              sGraphUnits = '';
            }
            var sTooltip = sBarLabel + '<br/><strong>' + sBarValue + '</strong> ' + sGraphUnits;

            showTooltip( item.pageX, item.pageY, item.series.color, sTooltip );
          }
        }
        else
        {
          $( '#tooltip' ).remove();
          tPreviousTooltip.dataIndex = null;
          tPreviousTooltip.seriesLabel = null;
        }
      };

      // Attach handler to graph div
      tGraphDiv.on( "plothover", onPlotHover );

      // Clear previous tooltip and draw the plot
      $( '#tooltip' ).remove();
      $.plot( tGraphDiv, aDataset, tOptions );
    }
    else
    {
      tGraphDiv.html('');

      // Set up underlying structure for bar graph display
      var aBars = [];
      for ( var sRowLabel in tGraphData )
      {
        var tRow = tGraphData[sRowLabel];
        if ( tRow.units == sGraphUnits )
        {
          aBars.push( { label: sRowLabel, value: tRow.value } );
        }
      }

      // Update the graph display

      var svg = d3.select( '#' + sGraphId + ' .bar-graph' ).append( 'svg' ).attr( 'width', tGraphDiv.width() ).attr( 'height', tGraphDiv.height() );

      // var svg = d3.select( '#' + sGraphId + ' .bar-graph' ),
      var margin = {top: 20, right: 20, bottom: 30, left: 60},
      width = +svg.attr("width") - margin.left - margin.right,
      height = +svg.attr("height") - margin.top - margin.bottom;

      var x = d3.scaleBand().rangeRound([0, width]).padding(0.1),
      y = d3.scaleLinear().rangeRound([height, 0]);

      var g = svg.append("g")
        .attr("transform", "translate(" + margin.left + "," + margin.top + ")");

      x.domain(aBars.map(function(d) { return d.label; }));
      y.domain([0, d3.max(aBars, function(d) { return d.value; })]);

      g.append("g")
          .attr("class", "axis axis--x")
          .attr("transform", "translate(0," + height + ")")
          .call(d3.axisBottom(x));

      g.append("g")
          .attr("class", "axis axis--y")
          .call(d3.axisLeft(y).ticks(10))
        .append("text")
          .attr("transform", "rotate(-90)")
          .attr("y", 6)
          .attr("dy", "0.71em")
          .attr("text-anchor", "end")
          .text("xxxxxxxxxxxx");

      g.selectAll(".bar")
        .data(aBars)
        .enter().append("rect")
          .attr("class", "bar")
          .attr("x", function(d) { return x(d.label); })
          .attr("y", function(d) { return y(d.value); })
          .attr("width", x.bandwidth())
          .attr("height", function(d) { return height - y(d.value); });
    }
  }
}

// Determine graph units based on prevalence in data structure
function pickGraphUnits( tGraphData )
{
  // Count occurrences of each units string in graph data structure
  var tUnits = {};
  for ( var sRowLabel in tGraphData )
  {
    var sUnits = tGraphData[sRowLabel].units;
    if ( sUnits in tUnits )
    {
      tUnits[sUnits] ++;
    }
    else
    {
      tUnits[sUnits] = 1;
    }
  }

  // Determine which units string occurs most
  var iVoteMax = 0;
  sGraphUnits = '';
  for ( var sUnits in tUnits )
  {
    if ( tUnits[sUnits] > iVoteMax )
    {
      sGraphUnits = sUnits;
    }

    iVoteMax = Math.max( iVoteMax, tUnits[sUnits] );
  }

  // Return prevalent units string
  return sGraphUnits;
}

// Advance to next row
function nextRow( bSuccess )
{
  // Clear highlighting
  $( '#bgt_table > tbody .' + g_sPendingClass ).removeClass( g_sPendingClass );
  $( '#bgt_table > tbody .' + g_sSuccessClass ).removeClass( g_sSuccessClass );

  // If current row contains new, live data, highlight it
  if ( bSuccess && ! g_tCachedValues )
  {
    $( '#row_' + g_iRow ).addClass( g_sSuccessClass );
  }

  // Advance row index
  if ( g_iRow < ( g_aRows.length - 1 ) )
  {
    // Advance to next row
    g_iRow ++;
    if ( ! g_tCachedValues )
    {
      enableRefreshButton( true );
    }
  }
  else
  {
    // Finished last row
    g_iRow = 0;
    g_tCachedValues = null;
    stopRefresh();
    enableRefreshButton( true );
    $( '.sortable' ).removeClass( 'sorter-false' );
    console.log( '=> View initialized in ' + ( Date.now() - g_iStartTime ) + ' ms' );
  }

  // Reinitialize variables
  g_iInstanceOffset = 2;
  g_aRowData = [];

  // Update tablesorter event handlers
  g_tTable.off( 'sortEnd' );
  g_tTable.off( 'filterEnd' );
  g_tTable.on( 'sortEnd', onSortEnd );
  g_tTable.on( 'filterEnd', onFilterEnd );
  g_tTable.on( 'tablesorter-ready', onTablesorterReady );

  // Trigger event to update tablesorter cache
  g_tTable.trigger( 'update' );
}

function onSortEnd( tEvent )
{
  updateGraphs( false );
}

function onFilterEnd( tEvent )
{
  updateGraphs( false );
  updateRowCount();
  $( window ).scrollTop( 0 );
}

function onTablesorterReady()
{
  g_tTable.off( 'tablesorter-ready' );

  // Show row count in toolbar
  updateRowCount();

  if ( g_tCachedValues || g_bRefreshing )
  {
    rq();
  }
}

function updateRowCount()
{
  // Show row count
  var nRows = $( '#bgt_table tbody tr:not(.filtered)' ).length;
  $( '.bgt_table_row_count' ).html( nRows );

  // Show/hide plural form
  if ( nRows == 1 )
  {
    $( '#bgt_table_row_count_s' ).hide();
  }
  else
  {
    $( '#bgt_table_row_count_s' ).show();
  }
}

function toggleRefresh()
{
  if ( g_bRefreshing )
  {
    stopRefresh();
  }
  else
  {
    startRefresh();
  }
}

function startRefresh()
{
  $( '.bgt_table_start_refresh_icon' ).hide();
  $( '.bgt_table_stop_refresh_icon' ).show();
  g_bRefreshing = true;
  rq();
}

function stopRefresh()
{
  $( '.bgt_table_stop_refresh_icon' ).hide();
  $( '.bgt_table_start_refresh_icon' ).show();
  g_bRefreshing = false;
  enableRefreshButton( false );
}

function enableRefreshButton( bEnable )
{
  $( '.bgt_table_refresh_button' ).prop( 'disabled', ! bEnable );
}

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
    '/view/src/uploadSnapshot.php',
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
  window.location.href='view/src/downloadSnapshot.php?csv_basename=' + g_sCsvBasename + '&snapshot_id=' + tRsp;
}

// --> tablesorter sticky header -->

function getStickyWrapper()
{
  return $( '.tablesorter-sticky-wrapper' );
}

function onResizeTablePane()
{
  // Clip the wrapper
  var iWidth = g_tTablePane.width() - scrollbarWidth();
  var tStickyWrapper = getStickyWrapper();
  var iHeight = tStickyWrapper.height();
  tStickyWrapper.css( 'clip', 'rect(0px,' + iWidth + 'px,' + iHeight + 'px,0px)' );
}

function onScrollTablePane()
{
  // Fire resize event
  g_tTablePane.resize();
}

function onScrollWindow()
{
  var tStickyWrapper = getStickyWrapper();

  switch( g_sSplitMode )
  {
    case SPLIT_MODE_WIDE:
      onResizeTablePane();
      var iTop = g_tTablePane.offset().top + g_iTablesorterThemeTopShift;
      break;

    case SPLIT_MODE_NARROW:
      tStickyWrapper.css( 'clip', 'rect(0px,' + g_tTable.width() + 'px,' + tStickyWrapper.height() + 'px,0px)' );
      var tNavbar = $( 'nav.navbar.fixed-top' );
      var iNavbarHeight = parseInt( tNavbar.css( 'padding-top' ) ) + tNavbar.height() + parseInt( tNavbar.css( 'padding-bottom' ) );
      var iTop = Math.max( g_tTable.offset().top, iNavbarHeight + $( window ).scrollTop() );
      break;
  }

  tStickyWrapper.offset(
    {
      top: iTop,
      left: tStickyWrapper.offset().left
    }
  );
}

function scrollbarWidth()
{
  var div = $('<div style="width:50px;height:50px;overflow:hidden;position:absolute;top:-200px;left:-200px;"><div style="height:100px;"></div>');
  // Append our div, do our calculation and then remove it
  $('body').append(div);
  var w1 = $('div', div).innerWidth();
  div.css('overflow-y', 'scroll');
  var w2 = $('div', div).innerWidth();
  $(div).remove();
  return (w1 - w2);
}

// <-- tablesorter sticky header <--


function doNothing( tJqXhr, sStatus, sErrorThrown )
{
}

function rqFail( tJqXhr, sStatus, sErrorThrown )
{
  clearWaitCursor();
  handleAjaxError( tJqXhr, sStatus, sErrorThrown );
}

function handleAjaxError( tJqXhr, sStatus, sErrorThrown )
{
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
