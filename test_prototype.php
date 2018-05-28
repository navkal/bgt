<!-- Copyright 2018 BACnet Gateway.  All rights reserved. -->

<style>
  .spinner
  {
    position: absolute;
    left: 50%;
    top: 50%;
    z-index: 1051;
    margin: -75px 0 0 -75px;
    border: 16px solid #0079c2;
    border-radius: 50%;
    border-top: 16px solid #8dc63f;
    width: 100px;
    height: 100px;
    -webkit-animation: spin 2s linear infinite;
    animation: spin 2s linear infinite;
  }

  @-webkit-keyframes spin
  {
    0% { -webkit-transform: rotate(0deg); }
    100% { -webkit-transform: rotate(360deg); }
  }

  @keyframes spin
  {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
  }
</style>

<script>

  $( document ).ready( clearWaitCursor );

  function rq()
  {
    setWaitCursor();

    var sArgList =
        '?facility=ahs'
      + '&instance=3006238';

    // Issue request to BACnet Gateway
    $.ajax(
      'http://<?=$_SESSION['bgt']['host']?>:<?=$_SESSION['bgt']['port']?>/bg.php/' + sArgList,
      {
        method: 'GET',
        processData: false,
        contentType: false,
        dataType : 'jsonp'
      }
    )
    .done( readDone )
    .fail( readFail );
  }

  function readDone( tRsp, sStatus, tJqXhr )
  {
    console.log( tRsp );
    clearWaitCursor();

    var sHtml = '<table class="table">';
    for ( sLabel in tRsp )
    {
      sHtml += '<tr>';
      sHtml += '<td>' + sLabel + '</td>';
      sHtml += '<td>' + JSON.stringify( tRsp[sLabel] ) + '</td>';
      sHtml += '</tr>';
    }

    $( '#response' ).append( sHtml );
  }

  function readFail( tJqXhr, sStatus, sErrorThrown )
  {
    clearWaitCursor();
    $( '#response' ).append( '<tr><td>error</td></tr>' );

    console.log( "=> ERROR=" + sStatus + " " + sErrorThrown );
    console.log( "=> HEADER=" + JSON.stringify( tJqXhr ) );
  }

  function setWaitCursor()
  {
    $( '#view' ).css( 'cursor', 'wait' );
    $( '#spinner' ).css( 'display', 'block' );
  }

  function clearWaitCursor()
  {
    $( '#view' ).css( 'cursor', 'default' );
    $( '#spinner' ).css( 'display', 'none' );
  }
</script>

<div class="container">
  <button type="button" class="btn btn-default" title="Send a Request to the BACnet Gateway" onclick="rq();" >Test BACnet Gateway</button>

  <br/>
  <br/>
  <br/>

  <div class="panel panel-default">
    <div class="panel-heading">
      <h3 class="panel-title">Gateway Response</h3>
    </div>
    <div class="panel-body">
      <table id="response" class="table">
      </table>
    </div>
  </div>

  <div id="spinner" class="spinner" >
  </div>
</div>
