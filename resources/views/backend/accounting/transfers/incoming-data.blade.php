<input type="hidden" hidden name="transfer" value="{{$info->transfer_id ?? ''}}">

<div class="col-md-12">
    <div class="table-responsive">
        <table id="order-table" class="table table-bordered">
            <thead>
                <tr>
                    <th class="text-center">{{ _lang('Product') }}</th>
                    <th class="text-center">{{ _lang('Quantity') }}</th>
                    <th class="text-center">{{ _lang('Receive') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $item)
                    <tr>
                        <td class="text-center">{{$item->item_name}}</td>
                        <td class="text-center">{{$item->quantity}}</td>
                        <td class="text-center">
                            <input type="number" class="form-control"  min="0" max="{{$item->quantity}}"
                            value="" name="products[{{$item->product_id}}]">
                        </td>
                    </tr>                                                
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="col-md-12">
    <div class="form-group">
        <label class="control-label">{{ _lang('Note') }}</label>
        <textarea class="form-control" readonly>{{$info->note ?? ''}}</textarea>
    </div>
</div>

<div class="col-md-12">
    <div class="form-group">
        <button type="submit" class="btn btn-primary btn-lg"><i class="ti-save-alt"></i> {{ _lang('Save') }}</button>
    </div>
</div>