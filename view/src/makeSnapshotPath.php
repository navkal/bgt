<?php
  // Copyright 2018 Building Monitor.  All rights reserved.

  require_once $_SERVER['DOCUMENT_ROOT'].'/../common/util.php';

  function makeSnapshotPath( $sCsvBasename, $sUniqId )
  {
    return sys_get_temp_dir() . '/' . $sCsvBasename . '_' . $sUniqId . '.csv';
  }
?>
