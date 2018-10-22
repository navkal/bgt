<?php
  // Copyright 2018 BACnet Gateway.  All rights reserved.
?>
<!-- Graph Options modal dialog -->
<div class="modal fade" id="graphOptionsDialog" tabindex="-1" role="dialog" aria-labelledby="graphOptionsLabel" aria-hidden="true">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="graphOptionsLabel"><span id="graphOptionsGraphName"></span> Graph Options</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form onsubmit="onSubmitGraphOptions(event); return false;" >

          <div class="form-group">
            <label for="baselineDatepicker" >Show delta since</label>
            <div id="baselineDatepicker" class="input-group mb-3 date">
              <div class="input-group-prepend">
                <button class="btn btn-outline-secondary" type="button">
                  <i class="far fa-calendar-alt" style="font-size:20px"></i>
                </button>
              </div>
              <input type="text" class="form-control" readonly>
            </div>
          </div>

          <div class="form-group form-check" style="padding-top:15px;">
            <input type="checkbox" class="form-check-input" id="showAsCost" onchange="onChangeShowAsCost()" />
            <label class="form-check-label" for="showAsCost">Show as cost</label>
          </div>

          <div class="form-group" >
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text">$</span>
              </div>
              <input id="dollarsPerUnit" class="form-control" type="number" min="0.01" step="0.01" onchange="onChangeDollarsPerUnit()" required />
              <div class="input-group-append">
                <span class="input-group-text">per unit</span>
              </div>
            </div>
          </div>

          <button id="graphOptionsSubmitButton" type="submit" style="display:none" ></button>

        </form>
      </div>
      <div class="modal-footer">
        <div>
          <button type="button" class="btn btn-primary" onclick="$('#graphOptionsSubmitButton').click()" >Set Options</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        </div>
      </div>
    </div>
  </div>
</div>
