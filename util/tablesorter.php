<?php
  // Copyright 2018 BACnet Gateway.  All rights reserved.

  // Common code for pages that use tablesorter
?>

<style>
  .bg-row-success
  {
    background-color: #f0fff0;
    border: 1px solid #00e600;
  }
</style>

<script>
var g_tTableProps =
  {
    theme : "dropbox",
    headerTemplate : '{content} {icon}',
    widgets : [ "uitheme", "resizable", "filter" ],
    widgetOptions :
    {
      resizable: true,
      filter_reset : ".reset",
      filter_cssFilter: "form-control"
    }
  };
</script>

<!-- tablesorter theme -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery.tablesorter/2.29.0/css/theme.dropbox.min.css" integrity="sha256-VFOuP1wPK9H/EeQZEmYL0TZlkMtUthqMBdrqfopliF4=" crossorigin="anonymous" />

<!-- tablesorter basic libraries -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.tablesorter/2.29.0/js/jquery.tablesorter.min.js" integrity="sha256-UD/M/6ixbHIPJ/hTwhb9IXbHG2nZSiB97b4BSSAVm6o=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.tablesorter/2.29.0/js/jquery.tablesorter.widgets.min.js" integrity="sha256-/3WKCLORjkqCd7cddzHbnXGR31qqys81XQe2khfPvTY=" crossorigin="anonymous"></script>
