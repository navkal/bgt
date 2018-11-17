<?php
  // Copyright 2018 Building Energy Monitor.  All rights reserved.

  @session_start();
  if ( ! isset( $_SESSION['bgt'] ) )
  {
    $_SESSION['bgt'] =
      [
        'host' => isset( $_REQUEST['host'] ) ? $_REQUEST['host'] : ( $_SERVER['SERVER_ADDR'] == '::1' ? '127.0.0.1' : $_SERVER['SERVER_ADDR'] ),
        'port' => isset( $_REQUEST['port'] ) ? $_REQUEST['port'] : '8000'
      ];
  }

  error_log( '==> Building Energy Gateway Host: ' . $_SESSION['bgt']['host'] );
  error_log( '==> Building Energy Gateway Port: ' . $_SESSION['bgt']['port'] );

  $_SESSION['bgt']['bgt_'] = strpos( $_SERVER['DOCUMENT_ROOT'], '/bgt_' ) !== false;
  //$_SESSION['bgt']['bgt_'] = true; // <------ Uncomment this line to test P&F interface

  define( 'BOOTSTRAP_VERSION', '_4' );

  define( 'LAYOUT_MODE_TAB', 'tab' );
  define( 'LAYOUT_MODE_SPLIT', 'split' );
  $g_sLayoutModeDefault = LAYOUT_MODE_SPLIT;

  // Load jQuery library outside the document head.  split.js needs this; don't know why.
  require_once '../common/libraries' . BOOTSTRAP_VERSION . '.php';  // <-- Includes jQuery, redundant with document head; needed by split.js (don't know why)

  if ( $_SESSION['bgt']['bgt_'] )
  {
    define( 'NAVBAR_EXPAND_CLASS', 'navbar-expand-custom' );
?>
    <!-- CSS for P&F interface -->
    <link rel="stylesheet" href="bgt_.css?version=<?=time()?>">
<?php
  }
?>

<!-- General CSS -->
<link rel="stylesheet" href="bgt.css?version=<?=time()?>">

<?php
  include "../common/main.php";
?>

<script>
  var g_sTemperatureUnknown = '??';


  //$( document ).ready( makeWeatherStationLink );

  // Append weather station link to navbar
  function makeWeatherStationLink()
  {
    var sHtml = '';
    sHtml += '<li id="weatherStationLink" class="nav-item">';
    sHtml += '<a id="weatherStationButton" class="btn btn-sm ml-5 mt-1" title="AHS Weather Station" target="_blank" href="https://owc.enterprise.earthnetworks.com/OnlineWeatherCenter.aspx?aid=5744" >';
    sHtml += '<span id="weatherStationValue">&nbsp;&nbsp;</span>&nbsp;&deg;F';
    sHtml += '</a>';
    sHtml += '</li>';
    $( '.navbar.fixed-top .navbar-nav.mr-auto' ).append( sHtml );
    getTemperature();
  }

  function getTemperature()
  {
    var sArgList =
        '?facility=ahs-ws'
      + '&instance=temperature'
      + '&live';

    // Issue request to Building Energy Gateway
    $.ajax(
      'http://<?=$_SESSION['bgt']['host']?>:<?=$_SESSION['bgt']['port']?>/' + sArgList,
      {
        method: 'GET',
        processData: false,
        contentType: false,
        dataType : 'jsonp'
      }
    )
    .done( getTemperatureDone )
    .fail( getTemperatureFail );
  }

  function getTemperatureDone( tRsp, sStatus, tJqXhr )
  {
    var sValue = g_sTemperatureUnknown;

    // Extract temperature value from response
    var tInstanceRsp = tRsp.instance_response;

    if ( tInstanceRsp.success )
    {
      var tData = tInstanceRsp.data;

      if ( tData.success )
      {
        sValue = Math.round( tData[tData.property] );

        if ( sValue >= 85 )
        {
          $( '#weatherStationButton' ).removeClass( 'btn-outline-success' ).addClass( 'btn-outline-danger' );
        }
        else
        {
          $( '#weatherStationButton' ).addClass( 'btn-outline-success' ).removeClass( 'btn-outline-danger' );
        }
      }
    }

    // Load temperature value into display
    $( '#weatherStationValue' ).text( sValue );

    setTimeout( getTemperature, 60000 );
  }

  function getTemperatureFail( tJqXhr, sStatus, sErrorThrown )
  {
    console.log( "=> ERROR=" + sStatus + " " + sErrorThrown );
    console.log( "=> HEADER=" + JSON.stringify( tJqXhr ) );

    setTimeout( getTemperature, 10000 );
  }
</script>
<style>
/* Style weather station link */
@media( max-width: <?=($_SESSION['bgt']['bgt_'])?1050:780?>px )
{
  #weatherStationLink
  {
    display: none;
  }
}
</style>