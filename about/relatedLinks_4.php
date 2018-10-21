<?php
  // Copyright 2018 BACnet Gateway.  All rights reserved.

  $sDtClass = 'dl-title col-sm-5 col-md-4 col-lg-3';
  $sDdClass = 'col-sm-7 col-md-8 col-lg-9';
?>

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
