@extends('layouts.app')

@section('content')

@php $date_format = get_option('date_format','Y-m-d'); @endphp

<div class="row">
	<div class="col-12">
		<div class="card">
			<div class="card-header">
				{{ _lang('My Profile') }}
			</div>
			
			<div class="card-body">
				<table class="table table-bordered" width="100%">
					<tbody>
						<tr class="text-center">
							<td colspan="2"><img class="thumb-image-md" src="{{ profile_picture($profile->profile_picture) }}"></td>
						</tr>
							<tr>
								<td>{{ _lang('Name') }}</td>
								<td>{{ $profile->name }}</td>
							</tr>
							<tr>
								<td>{{ _lang('Email') }}</td>
								<td>{{ $profile->email }}</td>
							</tr>
							<tr>
								<td>{{ _lang('User Type') }}</td>
								<td>{{ ucwords($profile->user_type) }}</td>
							</tr>

							@if(Auth::user()->user_type == 'user')
							<tr>
								<td>{{ _lang('Valid Until') }}</td>
								<td>{{ $profile->validUntil() }}</td>
							</tr>
							@endif
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
@endsection