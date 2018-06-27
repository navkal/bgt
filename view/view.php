<?php
  // Copyright 2018 BACnet Gateway.  All rights reserved.

  include $_SERVER['DOCUMENT_ROOT'] . '/util/tablesorter.php';

  // Convert column name list to JSON
  $sColNames = json_encode( $g_aColNames );

  // Read CSV file describing data to be retrieved and presented
  $file = fopen( $g_sCsvFilename, 'r' );
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
    return strcmp( $aLine1[0], $aLine2[0] );
  }
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/d3/4.13.0/d3.js" integrity="sha256-j2LsvgOlQFIb2Mphb+tX7d5pNmFdpsJU+s5GNo3z63g=" crossorigin="anonymous"></script>


<script src="https://cdnjs.cloudflare.com/ajax/libs/flot/0.8.3/jquery.flot.min.js" integrity="sha256-LMe2LItsvOs1WDRhgNXulB8wFpq885Pib0bnrjETvfI=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/flot/0.8.3/jquery.flot.symbol.min.js" integrity="sha256-Bm23OLMJlgAQ1BPlnkQZeAaRzEdEJXPakaKte3tujaw=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/flot/0.8.3/jquery.flot.resize.min.js" integrity="sha256-EM0o7Qv7O213xqRbn8IFc6QsSr02kAX1/z7musSfxx8=" crossorigin="anonymous"></script>
<script type="text/javascript" src="/util/jquery.flot.axislabels.js"></script>

<script>

  var g_aRows = null;
  var g_aColNames = null;
  var g_iInstanceOffset = 0;
  var g_iRow = 0;
  var g_iTimeoutMs = 0;
  var g_aRowData = [];
  var g_tGraphData = {};
  var g_sGraphUnits = '';

  var g_sSuccessClass = 'bg-row-success';
  var g_sPendingClass = 'bg-row-pending';

  $( document ).ready( onDocumentReady );

  function onDocumentReady()
  {
    // Load list of column names
    g_aColNames = JSON.parse( '<?=$sColNames?>' );

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
      $( '#value_' + g_iRow + '_' + iPair ).html( ( tData.presentValue == '' ) ? '' : Math.round( tData.presentValue ) );
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
    var tGraphs = $( '.bar-graph' );

    // Iterate over all graphs
    for ( var iGraph = 0; iGraph < tGraphs.length; iGraph ++ )
    {
      // Find index into row data that corresponds to target graph
      for ( var iData in g_aColNames )
      {
        var sGraphId = $( tGraphs[iGraph] ).parent().attr( 'id' );

        // If current index corresponds to target graph, update the graph
        if ( sGraphId == g_aColNames[iData].graph_id )
        {
          updateGraph( sGraphId, iData );
          break;
        }
      }
    }
  }

  function updateGraph( sGraphId, iData )
  {
    var sGraphName = g_aColNames[iData].value_col_name;
    var tBarData = g_aRowData[iData];

    // If data structure for target graph does not exist, create it
    if ( ! ( sGraphId in g_tGraphData ) )
    {
      g_tGraphData[sGraphId] = {};
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
      // Insert value into graph data structure
      tGraphData[sRowLabel] = { value: Math.round( tBarData.presentValue ), units: tBarData.units };
    }

    // Determine which units to show in graph
    setGraphUnits( tGraphData );

    var tGraphDiv = $( '#' + sGraphId + ' .bar-graph' );

    if ( true )
    {
      var data = [];
      var ticks = [];
      var iOffset = 0;
      for ( var sRowLabel in tGraphData )
      {
        var tRow = tGraphData[sRowLabel];
        if ( tRow.units == g_sGraphUnits )
        {
          data.push( [ iOffset, tRow.value ] );
          ticks.push( [ iOffset, sRowLabel ] );
          iOffset ++;
        }
      }

        var dataset = [{ label: '&nbsp;' + sGraphName, data: data, color: "#54b9f8" }];


        var options = {
            series: {
                bars: {
                    show: true
                }
            },
            bars: {
                align: "center",
                barWidth: 0.7
            },
            xaxis: {
                axisLabel: "<?=$g_sFirstColName?>",
                axisLabelUseCanvas: true,
                axisLabelFontSizePixels: 12,
                axisLabelFontFamily: 'Verdana, Arial',
                axisLabelPadding: 20,
                ticks: ticks
            },
            yaxis: {
                axisLabel: g_sGraphUnits,
                axisLabelUseCanvas: true,
                axisLabelFontSizePixels: 12,
                axisLabelFontFamily: 'Verdana, Arial',
                axisLabelPadding: 18,
                tickFormatter: function (v, axis) {
                    return v.toLocaleString();
                }
            },
            legend: {
                noColumns: 0,
                labelBoxBorderColor: "#1fa2f9",
                position: "ne"
            },
            grid: {
                hoverable: true,
                borderWidth: 2,
                backgroundColor: { colors: ["#ffffff", "#e7f5fe"] }
            }
        };

            $.plot(tGraphDiv, dataset, options);
            tGraphDiv.UseTooltip();

    }
    else
    {
      tGraphDiv.html('');

      // Set up underlying structure for bar graph display
      var aBars = [];
      for ( var sRowLabel in tGraphData )
      {
        var tRow = tGraphData[sRowLabel];
        if ( tRow.units == g_sGraphUnits )
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

  function setGraphUnits( tGraphData )
  {
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

    var iVoteMax = 0;
    g_sGraphUnits = '';
    for ( var sUnits in tUnits )
    {
      if ( tUnits[sUnits] > iVoteMax )
      {
        g_sGraphUnits = sUnits;
      }

      iVoteMax = Math.max( iVoteMax, tUnits[sRowLabel] );
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






















          function gd(year, month, day) {
            return new Date(year, month, day).getTime();
        }

        var previousPoint = null, previousLabel = null;

        $.fn.UseTooltip = function () {
            $(this).bind("plothover", function (event, pos, item) {
                if (item) {
                    if ((previousLabel != item.series.label) || (previousPoint != item.dataIndex)) {
                        previousPoint = item.dataIndex;
                        previousLabel = item.series.label;
                        $("#tooltip").remove();

                        var x = item.datapoint[0];
                        var y = item.datapoint[1];

                        var color = item.series.color;

                        //console.log(item.series.xaxis.ticks[x].label);

                        showTooltip(item.pageX,
                        item.pageY,
                        color,
                        item.series.xaxis.ticks[x].label + " : <strong>" + y.toLocaleString() + "</strong> " + g_sGraphUnits );
                    }
                } else {
                    $("#tooltip").remove();
                    previousPoint = null;
                }
            });
        };

        function showTooltip(x, y, color, contents) {
            $('<div id="tooltip">' + contents + '</div>').css({
                position: 'absolute',
                display: 'none',
                top: y+10,
                left: x-50,
                border: '2px solid ' + color,
                padding: '3px',
                'font-size': '9px',
                'border-radius': '5px',
                'background-color': '#fff',
                'font-family': 'Verdana, Arial, Helvetica, Tahoma, sans-serif',
                opacity: 0.9
            }).appendTo("body").fadeIn(200);
        }



</script>

<style>
  .bg-row-pending
  {
    color: #a6a6a6 !important;
  }

  .bar-graph
  {
    border: 1px dashed red;
    width: 90%;
    height: 430px;
    margin-left: auto;
    margin-right: auto;
    cursor: pointer;
  }

.bar {
  fill: steelblue;
}

.bar:hover {
  fill: brown;
}


</style>


<div class="container-fluid">

  <ul class="nav nav-tabs">

    <?php
      $bTableTab = false;
      foreach ( $g_aColNames as $aColPair )
      {
        if ( isset( $aColPair['graph_id'] ) )
        {
          if ( ! $bTableTab )
          {
            $bTableTab = true;
    ?>
            <li class="active"><a data-toggle="tab" href="#tableTab">Table</a></li>
    <?php
          }
    ?>
          <li><a data-toggle="tab" href="#<?=$aColPair['graph_id']?>"><?=$aColPair['value_col_name']?></a></li>
    <?php
        }
      }
    ?>
  </ul>

  <br/>

  <div class="tab-content">

    <div id="tableTab" class="tab-pane fade in active">
      <?php
        include $_SERVER['DOCUMENT_ROOT'] . '/view/table.php';
      ?>
    </div>

    <?php
      foreach ( $g_aColNames as $aColPair )
      {
        if ( isset( $aColPair['graph_id'] ) )
        {
    ?>
          <div id="<?=$aColPair['graph_id']?>" class="tab-pane fade">
            <div class="bar-graph" >
            </div>
          </div>
    <?php
        }
      }
    ?>

  </div>
</div>
