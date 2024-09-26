<form method="post" class="ajax-screen-submit" autocomplete="off" action="{{ action('UserController@update', $id) }}"
    enctype="multipart/form-data">
    {{ csrf_field()}}
    <input name="_method" type="hidden" value="PATCH">

    <div class="row p-2">
        <div class="col-md-12">
            <div class="form-group">
                <label class="control-label">{{ _lang('Name') }}</label>
                <input type="text" class="form-control" name="name" value="{{ $user->name }}" required>
            </div>
        </div>

        <div class="col-md-12">
            <div class="form-group">
                <label class="control-label">{{ _lang('Email') }}</label>
                <input type="text" class="form-control" name="email" value="{{ $user->email }}" required>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label class="control-label">{{ _lang('Password') }}</label>
                <input type="password" class="form-control" name="password" value="">
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label class="control-label">{{ _lang('Confirm Password') }}</label>
                <input type="password" class="form-control" name="password_confirmation">
            </div>
        </div>

        <div class="col-md-12">
            <div class="form-group">
                <label class="control-label">{{ _lang('User Type') }}</label>
                <select class="form-control select2 auto-select" data-selected="{{ $user->user_type }}" name="user_type"
                    required>
                    <option value="">{{ _lang('Select One') }}</option>
                    <option value="admin">{{ _lang('Admin') }}</option>
                    <option value="user">{{ _lang('User') }}</option>
                </select>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <a href="{{ route('roles.create') }}" data-reload="false" data-title="{{ _lang('Add User Role') }}" class="ajax-modal-2 select2-add"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>
                <label class="control-label">{{ _lang('User Role') }}</label>						
                <select class="form-control select2-ajax" data-value="id" data-display="name" data-table="staff_roles"  name="role_id" id="role_id">
                    <option value="">{{ _lang('Select One') }}</option>
                    {{ create_option("staff_roles","id","name", $user->role_id, array("company_id=" => company_id())) }}
                </select>
            </div>
        </div>

        <div class="col-md-12">
            <div class="form-group">
                <label class="control-label">{{ _lang('N° DUI') }}</label>
                <input type="text" class="form-control" name="dui" value="{{ $user->dui }}" placeholder="00000000-0" required>
            </div>
        </div>

        <div class="col-md-12">
            <div class="form-group">
                <label class="control-label">{{ _lang('Código Vendedor') }}</label>
                <input type="text" class="form-control" name="seller_code" value="{{ $user->seller_code }}" maxlength="30">
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label class="control-label">{{ _lang('Status') }}</label>
                <select class="form-control select2 auto-select" data-selected="{{ $user->status }}" name="status"
                    required>
                    <option value="">{{ _lang('Select One') }}</option>
                    <option value="1">{{ _lang('Active') }}</option>
                    <option value="0">{{ _lang('In Active') }}</option>
                </select>
            </div>
        </div>

        <div class="col-md-12">
            <div class="form-group">
                <label class="control-label">{{ _lang('Profile Picture') }}</label>
                <input type="file" class="form-control dropify" name="profile_picture" data-allowed-file-extensions="png jpg jpeg PNG JPG JPEG" data-default-file="{{ profile_picture($user->profile_picture) }}">
            </div>
        </div>

        <div class="form-group">
            <div class="col-md-12">
                <button type="submit" class="btn btn-primary btn-lg"><i class="ti-save-alt"></i> {{ _lang('Save Changes') }}</button>
            </div>
        </div>
    </div>
</form>
<script>
    $('input[name="dui"]').inputmask('99999999-9', {placeholder: '00000000-0'});
</script>