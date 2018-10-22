<?php
  // Copyright 2018 BACnet Gateway.  All rights reserved.
?>
<!-- Graph Options modal dialog -->
<div class="modal fade" id="graphOptionsDialog" tabindex="-1" role="dialog" aria-labelledby="graphOptionsLabel">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="graphOptionsLabel"><span id="graphOptionsGraphName"></span> Graph Options</h4>
      </div>
      <div class="modal-body">
        <form onsubmit="onSubmitGraphOptions(event); return false;" >
          <div class="form-group">
            <label class="control-label" for="baselineDatepicker" >Show delta since</label>
            <div id="baselineDatepicker" class="input-group date">
              <input type="text" class="form-control" readonly>
              <span class="input-group-addon btn btn-default">
                <i class="far fa-calendar-alt" style="font-size:20px"></i>
              </span>
            </div>
          </div>
          <div class="form-group" >
            <div class="checkbox" >
              <label>
                <input type="checkbox" id="showAsCost" onchange="onChangeShowAsCost()" />
                <b>Show as cost</b>
              </label>
            </div>
            <div class="input-group">
              <span class="input-group-addon">$</span>
              <input id="dollarsPerUnit" class="form-control" type="number" min="0.01" step="0.01" onchange="onChangeDollarsPerUnit()" required />
              <span class="input-group-addon">per unit</span>
            </div>
          </div>
          <button id="graphOptionsSubmitButton" type="submit" style="display:none" ></button>
        </form>
      </div>
      <div class="modal-footer">
        <div style="text-align:center;" >
          <button type="button" class="btn btn-primary" onclick="$('#graphOptionsSubmitButton').click()" >Set Options</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        </div>
      </div>
    </div>
  </div>
</div>
