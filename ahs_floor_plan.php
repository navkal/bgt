<?php
  // Copyright 2018 BACnet Gateway.  All rights reserved.
?>

<script>
  $( document ).ready( resizePdf );
  $( window ).resize( resizePdf );

  function resizePdf()
  {
    var iDocHeight = $( document ).height();
    var iNavbarHeight = $( '.navbar-fixed-top' ).height();
    var iFooterHeight = $( '.navbar-fixed-bottom' ).height();
    var iPdfHeight = iDocHeight - iNavbarHeight - iFooterHeight - 120;
    $( '#pdf' ).height( iPdfHeight );
  }
</script>

<div class="container-fluid">
  <a href="/ahs_floor_plan.pdf" target="_blank" class="pull-right" ><b>View in Another Tab</b></a>
</div>

</br>

<div id="pdf" width="100%" style="border:solid gray" >
  <embed src="/ahs_floor_plan.pdf" type="application/pdf" width="100%" height="100%" />
</div>
