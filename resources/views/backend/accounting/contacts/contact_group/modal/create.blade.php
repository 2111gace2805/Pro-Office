<form method="post" class="ajax-submit" autocomplete="off" action="{{route('contact_groups.store')}}" enctype="multipart/form-data">
	{{ csrf_field() }}
	
	<div class="col-md-12">
	  <div class="form-group">
		<label class="control-label">{{ _lang('Group Name') }}</label>						
		<input type="text" class="form-control" name="name" value="{{ old('name') }}" required>
	  </div>
	</div>

	<div class="col-md-12">
	  <div class="form-group">
		<label class="control-label">{{ _lang('Note') }}</label>						
		<textarea class="form-control" name="note">{{ old('note') }}</textarea>
	  </div>
	</div>
				
	<div class="col-md-12">
	  <div class="form-group">
	    
		<button type="submit" class="btn btn-primary btn-lg"><i class="ti-save-alt"></i> {{ _lang('Save Changes') }}</button>
	  </div>
	</div>
</form>
