<div id="modalDescargo{{$item->item_id}}" class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title mt-0">Informacion descargo</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body overflow-hidden">
                <div method="post" autocomplete="off">
                    <div class="row p-2 text-left">
                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label class="control-label">Declaración</label>
                                <input maxlength="64" type="text" class="form-control" name="no_declaracion[]" value="{{$item->no_declaracion}}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>