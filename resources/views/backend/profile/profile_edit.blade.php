@extends('layouts.app')
@section('content')
<div class="row">
	<div class="col-12">
		<div class="card">
			<div class="card-header">
				{{ _lang('Profile Settings') }}
			</div>
			<div class="card-body">
				<div class="row">
					<div class="col-md-6">
						<form action="{{ url('profile/update')}}" autocomplete="off" class="form-horizontal form-groups-bordered validate" enctype="multipart/form-data" method="post">
							@csrf
							<div class="form-group">
								<label class="control-label">{{ _lang('Name') }}</label>
								<input type="text" class="form-control" name="name" value="{{$profile->name}}" required>
							</div>
							<div class="form-group">
								<label class="control-label">{{ _lang('Email') }}</label>
								<input type="text" class="form-control" name="email" value="{{ $profile->email }}" required>
							</div>

							<div class="form-group">
								<label class="control-label">{{ _lang('Image') }} (300 X 300)</label>
								<input type="file" class="form-control dropify" data-default-file="{{ $profile->profile_picture != "" ? asset('public/uploads/profile/'.$profile->profile_picture) : '' }}" name="profile_picture" data-allowed-file-extensions="png jpg jpeg PNG JPG JPEG">
							</div>

							<div class="form-group">
								<button type="submit" class="btn btn-primary">{{ _lang('Update Profile') }}</button>
							</div>
						</form>
					</div>	
				</div>	
			</div>
		</div>
	</div>
</div>
@endsection

