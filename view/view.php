<?php
  // Copyright 2018 BACnet Gateway.  All rights reserved.

  include $_SERVER['DOCUMENT_ROOT'] . '/util/tablesorter.php';

  // Set up delta flags and retrieve baselines for graphs in view

  /////////////////////////////////////////////////////////////////////////////////////////////////////////
  // The baselines CSV file 'baselines/baselines.csv' is structured like so -
  // Column 1: Basename of CSV filename that describes a view
  // Columns 2-n: Names of columns in the view whose corresponding graphs should display delta values
  /////////////////////////////////////////////////////////////////////////////////////////////////////////

  // Get base name of CSV file that specifies the contents of this view
  $g_sCsvBasename = basename( $g_sCsvFilename, '.csv' );

  // Initialize list of baseline values
  $tBaselines = [];

  // Iterate over baselines CSV file
  $file = fopen( 'baselines/baselines.csv', 'r' );
  $bFound = false;
  while( ! feof( $file ) && ! $bFound )
  {
    $aLine = fgetcsv( $file );
    if ( is_array( $aLine ) && ( count( $aLine ) > 1 ) && ( $aLine[0][0] != '#' ) && ( $g_sCsvBasename == $aLine[0] ) )
    {
      // Current line starts with matching CSV filename
      $bFound = true;

      // Remove CSV filename from line array
      array_shift( $aLine );

      // Traverse array that describes current view
      for ( $iCol = 0; $iCol < count( $g_aColNames ); $iCol ++ )
      {
        // If this column has a graph, and its column name is in the list, try to retrieve baseline data
        if ( array_key_exists( 'graph', $g_aColNames[$iCol] ) && in_array( $g_aColNames[$iCol]['value_col_name'], $aLine ) )
        {
          // Format command
          $command = quote( getenv( 'PYTHON' ) ) . ' baselines/get_baseline.py 2>&1 -f ' . quote( $g_sCsvBasename ) . ' -c ' . $g_aColNames[$iCol]['value_col_name'];

          // Execute command
          error_log( '==> command=' . $command );
          exec( $command, $output, $status );
          error_log( '==> output=' . print_r( $output, true ) );

          // Set delta flag and save baseline values
          $tBaseline = json_decode( $output[ count( $output ) - 1 ] );
          if ( $g_aColNames[$iCol]['graph']['delta'] = ! empty( $tBaseline->values ) )
          {
            $tBaselines[$g_aColNames[$iCol]['graph']['graph_id']] = $tBaseline;
          }
        }
      }
    }
  }
  fclose( $file );



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

  // Sort lines
  usort( $aLines, "compareLines" );

  // Compare lines read from CSV file
  function compareLines( $aLine1, $aLine2 )
  {
    return strcmp( $aLine1[0], $aLine2[0] );
  }


  // Set flag to use flot or d3 to display bar graphs
  $bFlot = 1;
  if ( $bFlot )
  {
?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flot/0.8.3/jquery.flot.min.js" integrity="sha256-LMe2LItsvOs1WDRhgNXulB8wFpq885Pib0bnrjETvfI=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flot/0.8.3/jquery.flot.symbol.min.js" integrity="sha256-Bm23OLMJlgAQ1BPlnkQZeAaRzEdEJXPakaKte3tujaw=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flot/0.8.3/jquery.flot.resize.min.js" integrity="sha256-EM0o7Qv7O213xqRbn8IFc6QsSr02kAX1/z7musSfxx8=" crossorigin="anonymous"></script>
    <script type="text/javascript" src="/util/jquery.flot.axislabels.js"></script>
<?php
  }
  else
  {
?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/d3/4.13.0/d3.js" integrity="sha256-j2LsvgOlQFIb2Mphb+tX7d5pNmFdpsJU+s5GNo3z63g=" crossorigin="anonymous"></script>
<?php
  }
?>


<!-- CSS and JS libraries -->
<link rel="stylesheet" href="/view/view.css?version=<?=time()?>">
<script src="/view/view.js?version=<?=time()?>"></script>
<?php
  if ( $g_sLayoutMode == LAYOUT_MODE_SPLIT )
  {
?>
  <link rel="stylesheet" href="/lib/split/split.css?version=<?=time()?>">
  <script src="/lib/split/split.min.js"></script>
<?php
  }
?>


<!-- Constants -->
<script>
  var g_sCsvBasename = '<?=$g_sCsvBasename?>';
  var g_sFirstColName = '<?=$g_sFirstColName?>';
  var g_aColNames = JSON.parse( '<?=json_encode( $g_aColNames )?>' );
  var g_tBaselines = JSON.parse( '<?=json_encode( $tBaselines )?>' );
  var g_aRows = JSON.parse( '<?=json_encode( $aLines )?>' );
  var g_sLayoutMode = '<?=$g_sLayoutMode?>';
  var g_bFlot = <?=$bFlot?>;
  var g_sBacnetGatewayUrl = 'http://<?=$_SESSION['bgt']['host']?>:<?=$_SESSION['bgt']['port']?>/';
  var LAYOUT_MODE_SPLIT = '<?=LAYOUT_MODE_SPLIT?>';
  var LAYOUT_MODE_TAB = '<?=LAYOUT_MODE_TAB?>';
</script>


<!-- Layout -->
<div class="container-fluid">
<?php
  switch( $g_sLayoutMode )
  {
    case LAYOUT_MODE_TAB:
?>

  <!-- Tab layout -->
  <ul class="nav nav-tabs">

    <?php
      $bTableTab = false;
      foreach ( $g_aColNames as $aColPair )
      {
        if ( isset( $aColPair['graph'] ) )
        {
          if ( ! $bTableTab )
          {
            $bTableTab = true;
    ?>
            <li class="active"><a data-toggle="tab" href="#tableTab">Table</a></li>
    <?php
          }
    ?>
          <li><a class="graph-tab" data-toggle="tab" href="#<?=$aColPair['graph']['graph_id']?>"><?=$aColPair['value_col_name']?></a></li>
    <?php
        }
      }
    ?>
  </ul>

  <br/>

  <div class="tab-content">

    <div id="tableTab" class="tab-pane fade in active">
      <div class="container">
        <?php
          include $_SERVER['DOCUMENT_ROOT'] . '/view/table.php';
        ?>
      </div>
    </div>

    <?php
      foreach ( $g_aColNames as $aColPair )
      {
        if ( isset( $aColPair['graph'] ) )
        {
    ?>
          <div id="<?=$aColPair['graph']['graph_id']?>" class="tab-pane fade">
            <div class="bar-graph" >
            </div>
          </div>
    <?php
        }
      }
    ?>

  </div>

<?php
    break;
    case LAYOUT_MODE_SPLIT:
?>

  <!-- Split layout -->
  <div id="wide" class="backdrop">

    <div id="wideTablePane" class="split split-horizontal">
      <div class="split content">
        <?php
          include $_SERVER['DOCUMENT_ROOT'] . '/view/table.php';
        ?>
      </div>
    </div>

    <div id="wideGraphPane" class="split split-horizontal">
      <?php
        foreach ( $g_aColNames as $aColPair )
        {
          if ( isset( $aColPair['graph'] ) )
          {
      ?>
            <div id="<?=$aColPair['graph']['graph_id']?>" class="split content">
              <div class="bar-graph" >
              </div>
            </div>
      <?php
          }
        }
      ?>
    </div>

  </div>

  <div id="narrow" style="display:none" >
    <div id="narrowTablePane">
    </div>
    <br/>
    <div id="narrowGraphPane">
    </div>
  </div>

<?php
    break;
  }
?>

</div>
