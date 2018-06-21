<?php
  // Copyright 2018 BACnet Gateway.  All rights reserved.
?>

<div name="plotview" id="plotview" style="width:90%; height:430px; margin-left:auto; margin-right:auto; cursor: pointer;" ></div>
<br/>
<div name="overview" id="overview" style="width:90%; height:100px; margin-left:auto; margin-right:auto; cursor: pointer;" ></div>
<br/>
<div name="scrollbar" id="scrollbar" style="width:90%; height:28px; margin-left:auto; margin-right:auto;" ></div>

<style>
  #plotview,#overview,#scrollbar
  {
    border: 1px dashed red;
  }
</style>

<script>
function onPlotShow()
{
  console.log( 'SHOW' );
}
function onPlotShown()
{
  console.log( 'SHOWN' );
}
</script>
