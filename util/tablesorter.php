<?php
  // Copyright 2018 Building Energy Monitor.  All rights reserved.

  // Common code for pages that use tablesorter
?>

<style>
  /* Make fonts larger for squinty users */
  #bgt_table *,
  #advanced_table *
  {
    font-size: .84375rem; /* 13.5px*/
    line-height: 1.5;
  }

  /* Row styling to show pending status */
  .bg-row-pending
  {
    color: #a6a6a6 !important;
  }

  /* Row styling to show success status */
  .bg-row-success
  {
    background-color: #f0fff0;
    border: 1px solid #00e600;
  }

  /* Font Awesome icons used in toolbar above table */
  .btn.tablesorter-headerRow .fas,
  .btn.tablesorter-headerRow .far
  {
    font-size: 1rem; /* 16px */
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
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery.tablesorter/2.31.0/css/theme.dropbox.min.css" integrity="sha256-CbNE7knbzUGwr4jEImul6Ww8oP32d5W88KjDPoJUzdk=" crossorigin="anonymous" />

<!-- tablesorter basic libraries -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.tablesorter/2.31.0/js/jquery.tablesorter.min.js" integrity="sha256-Ae7jmRrbL3hf1J/y22SYMPtx0wMVbG4g3HtpjioYuyk=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.tablesorter/2.31.0/js/jquery.tablesorter.widgets.js" integrity="sha256-sg80NyaLmey6oXCdI+VhKtRMYkI//IMuua1N9pG9HI8=" crossorigin="anonymous"></script>
