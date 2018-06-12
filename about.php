<?php
  // Copyright 2018 BACnet Gateway.  All rights reserved.

  define( 'BM', '<i>Building Monitor</i>' );
  define( 'BG', '<i>BACnet Gateway</i>' );

  // Additional links exclusively for Andover Plant and Facilities Department
  $aLinkFiles = [];
  if ( $_SESSION['bgt']['bgt_'] )
  {
    $sLinkDir = '/links';
    $aLinkFiles = scandir( $_SERVER['DOCUMENT_ROOT'] . $sLinkDir );
    $aLinkText =
    [
      'ahs_floor_plan.pdf' =>
      [
        'dt' => 'AHS Floor Plan',
        'dd' => 'Layouts of Andover High School levels 1-4'
      ]
    ];
  }
?>

<div class="container">

  <br/>

  <p>
    The <a href="http://www.EnergizeAndover.com" target="_blank">Energize Andover</a> team is pleased to offer the <?=BM?> web application and the <?=BG?> web service for use by Andover Plant and Facilities (P&F) and Andover Public Schools (APS).
  </p>

  <br/>

  <p class="h4">
    <?=BM?> for Plant and Facilities
  </p>

  <p>
    The <?=BM?> application allows P&F technicians to monitor real-time parameters throughout APS buildings.
    <?=BM?> can improve operational efficiency by providing quick and easy access to critical data.
  </p>

  <br/>

  <p class="h4">
    <?=BG?> for Andover Public Schools
  </p>

  <p>
    The <?=BG?> web service provides controlled access to selected parameters within APS buildings.
    Students can use the web service to develop smart data analysis applications in the programming language of their choice.
  </p>

  <br/>

  <p>
    For more information about <?=BM?> and <?=BG?>, please email us at <a href="mailto:energizeAndover@gmail.com">energizeAndover@gmail.com</a>.
  </p>

  <br/>

  <p class="h4">
    Related Links
  </p>

  <dl class="dl-horizontal">
    <dt><a href="http://www.EnergizeAndover.com" target="_blank">Energize Andover</a></dt>
    <dd>Energy conservation program serving P&F and APS</dd>
    <dt><a href="http://10.12.4.98/" target="_blank">Metasys Data Analysis</a></dt>
    <dd>Analysis of data exported from <a href="http://www.johnsoncontrols.com/buildings/building-management/building-automation-systems-bas" target="_blank" >Metasys Building Automation System</a>.</dd>
    <?php
      foreach ( $aLinkFiles as $sFilename )
      {
        if ( ( $sFilename != '.' ) && ( $sFilename != '..' ) )
        {
          if ( isset( $aLinkText[$sFilename] ) )
          {
            $sDt = $aLinkText[$sFilename]['dt'];
            $sDd = $aLinkText[$sFilename]['dd'];
          }
          else
          {
            $sDt = $sFilename;
            $sDd = $sFilename;
          }
    ?>
          <dt><a href="<?=$sLinkDir . '/' . $sFilename?>" target="_blank"><?=$sDt?></a></dt>
          <dd><?=$sDd?></dd>
    <?php
        }
      }
    ?>

  </dl>

  <br/>

  <div class="row">
    <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
      <a href="http://www.EnergizeAndover.com" target="_blank"><img src="ea.jpg" class="img-responsive pull-right" alt="Energize Andover" style="max-width:100px"></a>
    </div>
    <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
      <img src="aps.jpg" class="img-responsive" alt="Andover Public Schools" style="max-width:100px">
    </div>
  </div>

</div>
