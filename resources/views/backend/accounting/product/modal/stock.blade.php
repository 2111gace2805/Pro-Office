
<form method="POST" action="{{ route('save-stock', ['id' => $id]) }}">
    @csrf
    <input type="hidden" name="id" value="{{ $item->id }}">


<div class="col-xl-6 col-md-6 col-sm-12 m-auto">
    <div class="card shadow-none" style="border:1px solid #e0e0e0;">
        <div class="card-body text-center">
            <h5>{{ $item->item_name }}</h5> 
            <div style="border-radius:10px 10px 0 0; min-height: 150px; border:1px solid #e0e0e0;">
                {{-- <img src="{{ url($item->product->image) }}" alt="no image" height="150" id="imagen-producto"> --}}
                <input type="file" class="form-control dropify" name="imagen" id="imagen-producto"
                        data-allowed-file-extensions="png jpg jpeg PNG JPG JPEG"
                        data-default-file="{{ asset($item->product->image) }}">
            </div>
        </div>
    </div>
</div>
<input type="hidden" name="sucursal" value="{{ $item->company_id }}">


<div class="form-group">
    <label for="sucursal">Sucursal</label>
    <select class="form-control" id="sucursal" name="sucursal" disabled>
        @foreach($companies as $key => $value)
            <option value="{{$value->id}}" {{ $value->id === $item->company_id ? 'selected' : '' }}>{{$value->company_name}}</option>
        @endforeach
    </select>
</div>

<div class="form-group">
    <label for="minimo">Mínimo de Producto</label>
    <input type="number" class="form-control" id="minimo" name="minimo" placeholder="Mínimo" value="{{ $existingStock ? $existingStock->minimo : '' }}">
</div>

<div class="form-group">
    <label for="maximo">Máximo de Producto</label>
    <input type="number" class="form-control" id="maximo" name="maximo" placeholder="Máximo" value="{{ $existingStock ? $existingStock->maximo : '' }}">
</div>

<div class="form-group">
    <label for="cantidad">Cantidad</label>
    <input type="number" class="form-control" disabled id="cantidad" name="cantidad" placeholder="Cantidad" value="{{ $existingStock ? $existingStock->quantity : '' }}">
</div>


<button type="submit" class="btn btn-primary">Guardar</button>
</form>