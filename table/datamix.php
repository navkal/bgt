<?php
  // Copyright 2018 BACnet Gateway.  All rights reserved.
?>

<div class="container-fluid">

  <ul class="nav nav-tabs">

    <?php
      $bTableTab = false;
      foreach ( $aColNames as $aColPair )
      {
        if ( isset( $aColPair['bar_graph_id'] ) )
        {
          if ( ! $bTableTab )
          {
            $bTableTab = true;
    ?>
            <li class="active"><a data-toggle="tab" href="#tableTab">Table</a></li>
    <?php
          }
    ?>
          <li><a data-toggle="tab" href="#<?=$aColPair['bar_graph_id']?>"><?=$aColPair['value_col_name']?></a></li>
    <?php
        }
      }
    ?>
  </ul>

  <br/>

  <div class="tab-content">

    <div id="tableTab" class="tab-pane fade in active">
      <?php
        include $_SERVER['DOCUMENT_ROOT'] . '/table/table.php';
      ?>
    </div>

    <?php
      foreach ( $aColNames as $aColPair )
      {
        if ( isset( $aColPair['bar_graph_id'] ) )
        {
    ?>
          <div id="<?=$aColPair['bar_graph_id']?>" class="tab-pane fade">
            <?php
              $g_sBarGraphId = $aColPair['bar_graph_id'] . '_div';
              include $_SERVER['DOCUMENT_ROOT'] . '/table/graph.php';
            ?>
          </div>
    <?php
        }
      }
    ?>

  </div>
</div>
