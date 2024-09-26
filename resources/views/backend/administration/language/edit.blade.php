@extends('layouts.app')

@section('content')
<h4 class="page-title">{{ _lang('Edit Translation') }}</h4>
<div class="row">
	<div class="col-12">
		<div class="card">
			<div class="card-body">
				<form method="post" class="validate" autocomplete="off" action="{{ action('LanguageController@update', $id) }}">
					@csrf
					<input name="_method" type="hidden" value="PATCH">
					<div class="row">
						@foreach( $language as $key => $lang )
						<div class="col-md-6">
							<div class="form-group">
								<label class="control-label">{{ ucwords($key) }}</label>						
								<input type="text" class="form-control" name="language[{{ str_replace(' ','_',$key) }}]" value="{{ $lang }}" required>
							</div>
						</div>
						@endforeach
							
						<div class="col-md-12">
							<div class="form-group">
								<button type="submit" class="btn btn-primary">{{ _lang('Save Translation') }}</button>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
@endsection
