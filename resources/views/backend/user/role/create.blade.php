@extends('layouts.app')

@section('content')
<h4 class="page-title">{{ _lang('Role Management') }}</h4>
		
<div class="row">
	<div class="col-lg-6">
		<div class="card">
			<div class="card-header">
				<h4 class="header-title">{{ _lang('Create Role') }}</h4>
			</div>
			<div class="card-body">
			    <form method="post" class="validate" autocomplete="off" action="{{ route('roles.store') }}" enctype="multipart/form-data">
					{{ csrf_field() }}
					<div class="row">
						<div class="col-md-12">
							<div class="form-group">
								<label class="control-label">{{ _lang('Name') }}</label>						
								<input type="text" class="form-control" name="name" value="{{ old('name') }}" required>
							</div>
						</div>

						<div class="col-md-12">
							<div class="form-group">
								<label class="control-label">{{ _lang('Description') }}</label>						
								<textarea class="form-control" name="description">{{ old('description') }}</textarea>
							</div>
						</div>

						
						<div class="col-md-12">
							<div class="form-group">
								
								<button type="submit" class="btn btn-primary btn-lg"><i class="ti-save-alt"></i> {{ _lang('Save Changes') }}</button>
							</div>
						</div>
					</div>			
			    </form>
			</div>
		</div>
    </div>
</div>
@endsection


