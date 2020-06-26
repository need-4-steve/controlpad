<div class="form-group">
    {!! Form::label('Phone Numbers', 'Phone Numbers') !!}
    <div id="phone-list" class="list-group bg-font">
    <li class="list-group-item bg-font">
        <div class="clear"></div>
        <div class="form-group">
            <label>Number</label>
            <input class="form-control" type="text" name="phones[0][number]">
        </div>
        <div class="form-group">
            <label>Type</label>
            <select name="phones[0][type]" class="form-control">
                <option>Mobile</option>
                <option>Land-line</option>
            </select>
        </div>
        <input type="hidden" name="phones[0][new]" value="1">
    </li>
        </div>
    <button type="button" class="btn btn-default margin-top-2 margin-bottom-2" id="add-phone"><i class="lnr lnr-plus"></i> Add Another Number</button>
</div>
