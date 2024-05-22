@extends('layouts.app')

@section('content')
<h4 class="page-title">{{ _lang('Add New Language') }}</h4>

<div class="row">
	<div class="col-md-6">
		<div class="card">	
			<div class="card-body">
				<form method="post" class="validate" autocomplete="off" action="{{ route('languages.store') }}" enctype="multipart/form-data">
					@csrf
					<div class="row">
						<div class="col-md-12">
							<div class="form-group">
								<label class="control-label">{{ _lang('Language Name') }}</label>						
								<input type="text" class="form-control" name="language_name" value="{{ old('language_name') }}" required>
							</div>
						</div>
						
						<div class="col-md-12">
							<div class="form-group">
								<button type="submit" class="btn btn-primary">{{ _lang('Create Language') }}</button>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
@endsection


