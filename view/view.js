  // Copyright 2018 BACnet Gateway.  All rights reserved.

  var g_iInstanceOffset = 0;
  var g_iRow = 0;
  var g_iTimeoutMs = 0;
  var g_aRowData = [];
  var g_tStartTime = new( Date );
  var g_tStartValues = {};
  var g_tGraphData = {};
  var g_bHorizontal = null;

  var g_sSuccessClass = 'bg-row-success';
  var g_sPendingClass = 'bg-row-pending';

  $( document ).ready( onDocumentReady );

  function onDocumentReady()
  {
    // Initialize layout framework
    switch( g_sLayoutMode )
    {
      case 'tab':
      default:
        // Initialize tabs
        initTabs();
        break;

      case 'split':
        // Initialize splitters
        initSplitters();
        break;

      case 'scroll':
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

  function initTabs()
  {
    var aGraphIds = [];

    for ( var iCol in g_aColNames )
    {
      var tCol = g_aColNames[iCol];
      if ( 'graph' in tCol )
      {
        aGraphIds.push( '#' + tCol['graph']['graph_id'] );
      }
    }

    if ( ! aGraphIds.length )
    {
      // Hide/remove tab styling
      $( '#view > .container-fluid .nav.nav-tabs' ).remove();
      $( '#view > .container-fluid .tab-content' ).removeClass();
      $( '#tableTab' ).removeClass();
    }
  }

  function initSplitters()
  {
    var aGraphIds = [];

    for ( var iCol in g_aColNames )
    {
      var tCol = g_aColNames[iCol];
      if ( 'graph' in tCol )
      {
        aGraphIds.push( '#' + tCol['graph']['graph_id'] );
      }
    }

    if ( aGraphIds.length )
    {
      // Set up splitter styling

      $( '#view > .container-fluid' ).css( 'height', '85%' );

      Split(
        ['#tablePane', '#graphPane'],
        {
          gutterSize: 8,
          minSize: 0,
          cursor: 'col-resize'
        }
      );

      if ( aGraphIds.length > 1 )
      {
        Split(
          aGraphIds,
          {
            direction: 'vertical',
            sizes: [50, 50],
            minSize: 0,
            gutterSize: 8,
            cursor: 'row-resize'
          }
        );
      }
    }
    else
    {
      // Remove splitter styling

      $( '#view > .container-fluid' ).prepend( '<br/>' );

      $( '#graphPane' ).hide();

      $( '#tablePane > .split.content' )
        .removeClass( 'split' )
        .removeClass( 'content' )
        .addClass( 'container' );

      $( '#tablePane' )
        .removeClass( 'split' )
        .removeClass( 'split-horizontal' );

      $( '#tablePane' )
        .parent()
        .removeClass( 'backdrop' );
    }
  }

  function initTable()
  {
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
  }

  function initGraphs()
  {
    g_bHorizontal = g_aRows.length > 10;

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

    // Set handler to update graphs when graph tab is selected
    $( 'a.graph-tab' ).on( 'shown.tab.bs', onGraphTabShown );
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
        updateGraphs();

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

  function updateGraphs()
  {
    console.log( 'updateGraphs()');
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

        // Update the graph data structure
        updateGraphData( sGraphId, g_aRowData[iGraph], bDelta );

        // If graph is visible, update the display
        var tGraphDiv = $( '#' + sGraphId + ' .bar-graph' );
        if ( tGraphDiv.is(':visible') )
        {
          var sGraphName = g_aColNames[iGraph].value_col_name;
          updateGraphDisplay( tGraphDiv, sGraphId, sGraphName, bDelta );
        }
      }
    }
  }

  function updateGraphData( sGraphId, tBarData, bDelta )
  {
    console.log( '==> updateGraphData id=' + sGraphId );

    // If data structure for target graph does not exist, create it
    if ( ! ( sGraphId in g_tGraphData ) )
    {
      g_tGraphData[sGraphId] = {};
      g_tStartValues[sGraphId] = {};
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
      // Save initial value in start data structure
      var tStartValues = g_tStartValues[sGraphId];
      if ( ! ( sRowLabel in tStartValues ) )
      {
        tStartValues[sRowLabel] = Math.round( tBarData.presentValue );
      }

      // Determine value to be shown in graph: raw value or delta since start
      var nValue =  Math.round( tBarData.presentValue );
      if ( bDelta )
      {
        nValue -= tStartValues[sRowLabel];
      }

      // Insert value into graph data structure
      tGraphData[sRowLabel] = { value: nValue, units: tBarData.units };
      console.log( '===> Inserted into ' + sGraphId + ': ' + JSON.stringify( tGraphData[sRowLabel] ) );
    }
  }

  function updateGraphDisplay( tGraphDiv, sGraphId, sGraphName, bDelta )
  {
    console.log( '==> updateGraphDisplay id=' + sGraphId );

    if ( sGraphId in g_tGraphData )
    {
      // Determine which units to show in graph
      var tGraphData = g_tGraphData[sGraphId];
      var sGraphUnits = pickGraphUnits( tGraphData );

      if ( g_bFlot )
      {
        var nBars = Object.keys( tGraphData ).length;
        var iOffset = g_bHorizontal ? ( nBars - 1 ) : 0;

        // Load data values and tick labels
        var aData = [];
        var aTicks = [];
        for ( var sRowLabel in tGraphData )
        {
          var tRow = tGraphData[sRowLabel];
          if ( tRow.units == sGraphUnits )
          {
            aData.push( g_bHorizontal ? [ tRow.value, iOffset ] : [ iOffset, tRow.value ] );
            aTicks.push( [ iOffset, sRowLabel ] );
            iOffset += g_bHorizontal ? -1 : 1;
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
