<?php
  // Copyright 2018 Building Energy Monitor.  All rights reserved.

  define( 'BEM', '<i>Building Energy Monitor</i>' );
  define( 'BG', '<i>BACnet Gateway</i>' );

  $aLinkFilenames = [];
  $sLinksPathRel = '/bgt_ln';
  $sLinksPathAbs = $_SERVER['DOCUMENT_ROOT'] . $sLinksPathRel;

  // Present additional links exclusively for Andover Plant and Facilities Department
  if ( $_SESSION['bgt']['bgt_'] && file_exists( $sLinksPathAbs ) )
  {
    $sLinkCsv = 'links.csv';

    // Get list of files in links directory
    $aLinkFilenames = scandir( $sLinksPathAbs );

    // Remove unwanted elements from list of links
    if ( ( $iLinkCsv = array_search( '..', $aLinkFilenames ) ) !== false )
    {
      unset( $aLinkFilenames[$iLinkCsv] );
    }
    if ( ( $iLinkCsv = array_search( '.', $aLinkFilenames ) ) !== false )
    {
      unset( $aLinkFilenames[$iLinkCsv] );
    }
    if ( ( $iLinkCsv = array_search( $sLinkCsv, $aLinkFilenames ) ) !== false )
    {
      unset( $aLinkFilenames[$iLinkCsv] );
    }

    // Open link description file and skip header line
    $file = fopen( $sLinksPathAbs . '/' . $sLinkCsv, 'r' );
    fgetcsv( $file );

    // Read link description file
    $aLinkDescr = [];
    while( ! feof( $file ) )
    {
      $aLine = fgetcsv( $file );
      $sLinkFilename = trim( $aLine[0] );

      // If this line is neither empty nor commented out, load descriptions
      if ( $sLinkFilename && ( substr( $sLinkFilename, 0, 1 ) != '#' ) )
      {
        $aLinkDescr[$sLinkFilename] = [ 'dt' => trim( $aLine[1] ), 'dd' => trim( $aLine[2] ) ];
      }
    }

    fclose( $file );
  }

  // Description list layout
  $sDtClass = 'dl-title col-sm-5 col-md-4 col-lg-3';
  $sDdClass = 'col-sm-7 col-md-8 col-lg-9';
?>

<div class="container">

  <br/>

  <p>
    The <a href="http://www.EnergizeAndover.com" target="_blank">Energize Andover</a> team is pleased to offer the <?=BEM?> web application and the <?=BG?> web service for use by Andover Plant and Facilities (P&F) and Andover Public Schools (APS).
  </p>

  <br/>

  <p class="h5">
    <?=BEM?> for Plant and Facilities
  </p>

  <p>
    The <?=BEM?> application allows P&F technicians to monitor real-time parameters throughout APS buildings.
    <?=BEM?> can improve operational efficiency by providing quick and easy access to critical data.
  </p>

  <br/>

  <p class="h5">
    <?=BG?> for Andover Public Schools
  </p>

  <p>
    The <?=BG?> web service provides controlled access to selected parameters within APS buildings.
    Students can use the web service to develop smart data analysis applications in the programming language of their choice.
  </p>

  <br/>

  <p class="h5">
    Email
  </p>

  <p>
    For more information about <?=BEM?> and <?=BG?>, please email us at <a href="mailto:energizeAndover@gmail.com">energizeAndover@gmail.com</a>.
  </p>

  <br/>

  <p class="h5">
    Related Links
  </p>

  <dl class="row">

    <dt class="<?=$sDtClass?>"><a href="http://www.EnergizeAndover.com" target="_blank">Energize Andover</a></dt>
    <dd class="<?=$sDdClass?>">Energy conservation program serving P&F and APS</dd>

    <dt class="<?=$sDtClass?>"><a href="http://10.12.4.98/" target="_blank">Metasys Data Analysis</a></dt>
    <dd class="<?=$sDdClass?>">Analysis of data exported from <a href="http://www.johnsoncontrols.com/buildings/building-management/building-automation-systems-bas" target="_blank" >Metasys Building Automation System</a>.</dd>

    <?php
      foreach ( $aLinkFilenames as $sFilename )
      {
        if ( isset( $aLinkDescr[$sFilename] ) )
        {
          $sDt = $aLinkDescr[$sFilename]['dt'];
          $sDd = $aLinkDescr[$sFilename]['dd'];
        }
        else
        {
          $sDt = $sFilename;
          $sDd = $sFilename;
        }
    ?>
        <dt class="<?=$sDtClass?>"><a href="<?=$sLinksPathRel . '/' . $sFilename?>" target="_blank"><?=$sDt?></a></dt>
        <dd class="<?=$sDdClass?>"><?=$sDd?></dd>
    <?php
      }
    ?>

  </dl>

  <table style="width:100%; margin-top:40px">
    <tr>
      <td style="padding-right:15px;" >
        <a href="http://www.EnergizeAndover.com" target="_blank"><img src="about/ea.jpg" class="img-responsive float-right" alt="Energize Andover" style="max-width:100px"></a>
      </td>
      <td style="padding-left:15px;" >
        <img src="about/aps.jpg" class="img-responsive" alt="Andover Public Schools" style="max-width:100px">
      </td>
    </tr>
  </table>

</div>
