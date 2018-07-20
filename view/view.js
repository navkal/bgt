// Copyright 2018 BACnet Gateway.  All rights reserved.

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

var g_tTable = null;
var g_iInstanceOffset = 0;
var g_iRow = 0;
var g_iTimeoutMs = 0;
var g_aRowData = [];
var g_tStartTime = new( Date );
var g_tBaselines = {};
var g_aGraphSelectors = null;
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

$( document ).ready( onDocumentReady );

function onDocumentReady()
{
  // Initialize list of graph IDs
  initGraphIds();

  // Initialize baseline values
  initBaselines();

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

  // Initialize table
  initTable();

  // Initialize graphs
  initGraphs();

  // Issue first request
  g_iInstanceOffset = 2;
  rq();
}

function initGraphIds()
{
  g_aGraphSelectors = [];

  for ( var iCol in g_aColNames )
  {
    var tCol = g_aColNames[iCol];
    if ( 'graph' in tCol )
    {
      g_aGraphSelectors.push( '#' + tCol['graph']['graph_id'] );
    }
  }
}

function initBaselines()
{
  console.log( '=> g_aGraphSelectors=' + JSON.stringify( g_aGraphSelectors ) );
  console.log( '=> g_aColNames=' + JSON.stringify( g_aColNames ) );

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
}

function wideToNarrow()
{
  // Hide the wide div
  $( '#wide' ).hide();

  // Move the table
  g_tNarrowTableParent.append( g_tTable );

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

function initTable()
{
  var sHtml = '';
  for ( var iRow in g_aRows )
  {
    // Create row
    sHtml += '<tr id="row_' + iRow + '">';

    // Create cell for label in first column
    sHtml += '<td class="row-label" >' + g_aRows[iRow][0] + '</td>';

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
  g_tTable = $( '#bgt_table' );
  g_tTable.tablesorter( g_tViewTableProps );
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

function onGraphTabShown( tEvent )
{
  var sGraphId = $( tEvent.target ).attr( 'href' ).substring( 1 );
  var tGraphDiv = $( '#' + sGraphId + ' .bar-graph' );

  var iGraph = getGraphIndex( sGraphId );

  if ( iGraph !== null )
  {
    var sGraphName = g_aColNames[iGraph].value_col_name;
    var bDelta = g_aColNames[iGraph].graph.delta;

    updateGraphDisplay( tGraphDiv, sGraphId, sGraphName, bDelta );
  }
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
      g_sBacnetGatewayUrl + sArgList,
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
  // Update pairs
  var iPair = 2;
  for ( var iData in g_aRowData )
  {
    var tData = g_aRowData[iData];

    // Decide how to display the value
    var value = null;
    if ( tData.presentValue === '' )
    {
      value = '';
    }
    else if ( ( -1 < tData.presentValue ) && ( tData.presentValue < 1 ) )
    {
      value = Math.round( tData.presentValue * 100 ) / 100;
    }
    else
    {
      value = Math.round( tData.presentValue );
    }

    $( '#value_' + g_iRow + '_' + iPair ).html( value );
    $( '#units_' + g_iRow + '_' + iPair ).html( tData.units );
    iPair ++;
  }

  // Update date
  var tDate = new Date;
  sTime = tDate.toLocaleString();
  $( '#time_' + g_iRow ).html( sTime );
}

function updateGraphs( bUpdateData )
{
  var aGraphs = $( '.bar-graph' );

  // Iterate over all graphs
  for ( var iGraph = 0; iGraph < aGraphs.length; iGraph ++ )
  {
    // Find the graph index
    var sGraphId = $( aGraphs[iGraph] ).parent().attr( 'id' );
    var iGraph = getGraphIndex( sGraphId );

    if ( iGraph !== null )
    {
      var bDelta = g_aColNames[iGraph].graph.delta;

      // Optionally update the graph data structure
      if ( bUpdateData )
      {
        updateGraphData( sGraphId, g_aRowData[iGraph], bDelta );
      }

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
    if ( bDelta )
    {
      console.log( '==> sGraphId=' + sGraphId );
      g_tBaselines[sGraphId] = {};
    }
  }

  // Update target graph data
  var tGraphData = g_tGraphData[sGraphId];
  var sRowLabel = g_aRows[g_iRow][0];

  if ( tBarData.presentValue == '' )
  {
    // No value; remove element from graph data structure
    delete tGraphData[sRowLabel];
  }
  else
  {
    // Get raw value
    var nValue =  Math.round( tBarData.presentValue );

    // Determine value to be shown in graph: raw value or delta since baseline
    if ( bDelta )
    {
      var tBaselines = g_tBaselines[sGraphId];
      if ( ! ( sRowLabel in tBaselines ) )
      {
        // Save initial value in baseline data structure
        tBaselines[sRowLabel] = nValue;
      }

      // Calculate delta value
      nValue -= tBaselines[sRowLabel];
    }

    // Insert value into graph data structure
    tGraphData[sRowLabel] = { value: nValue, units: tBarData.units };
  }
}

function updateGraphDisplay( tGraphDiv, sGraphId, sGraphName, bDelta )
{
  if ( sGraphId in g_tGraphData )
  {
    // Determine which units to show in graph
    var tGraphData = g_tGraphData[sGraphId];
    var sGraphUnits = pickGraphUnits( tGraphData );

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
            aData.push( g_bHorizontal ? [ tRow.value, iOffset ] : [ iOffset, tRow.value ] );
            aTicks.push( [ iOffset, sBarLabel ] );
            iOffset += g_bHorizontal ? -1 : 1;
          }
        }
      }

      // Define dataset consisting of one series
      var sSince = bDelta ? ' since ' + g_tStartTime.toLocaleString() : '';
      var aDataset = [ { label: '&nbsp;' + sGraphName + sSince, data: aData, color: "#54b9f8" } ];

      // Define tick formatter function
      var toLocaleString = function( v, axis )
      {
        return v.toLocaleString();
      };

      // Set up graph options
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
          axisLabel: ( g_bHorizontal ? sGraphUnits : g_sFirstColName ),
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
          axisLabel: g_bHorizontal ? g_sFirstColName : sGraphUnits,
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

            showTooltip(
              item.pageX,
              item.pageY,
              item.series.color,
              ( g_bHorizontal ? item.series.yaxis.ticks[iTick].label : item.series.xaxis.ticks[x].label )
              +
              "<br/><strong>"
              +
              ( g_bHorizontal ? x.toLocaleString() : y.toLocaleString() )
              +
              "</strong> "
              +
              ( g_bHorizontal ? item.series.xaxis.options.axisLabel : item.series.yaxis.options.axisLabel )
            );
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

function getGraphIndex( sGraphId )
{
  // Find index into row data that corresponds to target graph
  var bFound = false;
  for ( var iGraph = 0; ( iGraph < g_aColNames.length ) && ! bFound; iGraph ++ )
  {
    var tCol = g_aColNames[iGraph];
    bFound = ( 'graph' in tCol ) && ( sGraphId == tCol.graph.graph_id );
  }

  return bFound ? iGraph - 1 : null;
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

  // Update tablesorter event handlers
  g_tTable.off( 'sortEnd' );
  g_tTable.off( 'filterEnd' );
  g_tTable.on( 'sortEnd', onSortEnd );
  g_tTable.on( 'filterEnd', onFilterEnd );
  g_tTable.on( 'tablesorter-ready', onTablesorterReady );

  // Trigger event to update tabelsorter cache
  g_tTable.trigger( 'update' );
}

function onSortEnd( tEvent )
{
  updateGraphs( false );
}

function onFilterEnd( tEvent )
{
  updateGraphs( false );
}

function onTablesorterReady()
{
  g_tTable.off( 'tablesorter-ready' );
  g_tTable.show();
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
