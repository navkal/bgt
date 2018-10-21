<?php
  // Copyright 2018 BACnet Gateway.  All rights reserved.
?>

<dl class="dl-horizontal">

  <dt><a href="http://www.EnergizeAndover.com" target="_blank">Energize Andover</a></dt>
  <dd>Energy conservation program serving P&F and APS</dd>

  <dt><a href="http://10.12.4.98/" target="_blank">Metasys Data Analysis</a></dt>
  <dd>Analysis of data exported from <a href="http://www.johnsoncontrols.com/buildings/building-management/building-automation-systems-bas" target="_blank" >Metasys Building Automation System</a>.</dd>

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
      <dt><a href="<?=$sLinksPathRel . '/' . $sFilename?>" target="_blank"><?=$sDt?></a></dt>
      <dd><?=$sDd?></dd>
  <?php
    }
  ?>

</dl>
