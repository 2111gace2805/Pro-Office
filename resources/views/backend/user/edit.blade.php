@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">
                <h4 class="header-title">{{ _lang('Update User') }}</h4>
            </div>
            <div class="card-body">
                <form method="post" class="validate" autocomplete="off"
                    action="{{ action('UserController@update', $id) }}" enctype="multipart/form-data">
                    {{ csrf_field()}}
                    <input name="_method" type="hidden" value="PATCH">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group row">
                                <label class="col-xl-3 col-form-label">{{ _lang('Name') }}</label>
                                <div class="col-xl-9">
                                    <input type="text" class="form-control" name="name" value="{{ $user->name }}"
                                        required>
                                </div>
                            </div>


                            <div class="form-group row">
                                <label class="col-xl-3 col-form-label">{{ _lang('Email') }}</label>
                                <div class="col-xl-9">
                                    <input type="text" class="form-control" name="email" value="{{ $user->email }}"
                                        required>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-xl-3 col-form-label">{{ _lang('Password') }}</label>
                                <div class="col-xl-9">
                                    <input type="password" class="form-control" name="password">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-xl-3 col-form-label">{{ _lang('Confirm Password') }}</label>
                                <div class="col-xl-9">
                                    <input type="password" class="form-control" name="password_confirmation">
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-xl-3 col-form-label">{{ _lang('User Type') }}</label>
                                <div class="col-xl-9">
                                    <select class="form-control auto-select"
                                        data-selected="{{ $user->user_type }}" name="user_type" required>
                                        <option value="">{{ _lang('Select One') }}</option>
                                        <option value="admin">{{ _lang('Admin') }}</option>
                                        <option value="user">{{ _lang('User') }}</option>
                                    </select>
                                </div>
                            </div>  
                            
                            <div class="form-group row">
                                <label class="col-xl-3 col-form-label">{{ _lang('User Role') }}</label>
                                <div class="col-xl-9 d-flex">
                                    <select class="form-control select2-ajax" data-value="id" data-display="name"
                                        data-table="staff_roles"  id="role_id" name="role_id">
                                        {{ create_option("staff_roles","id","name", $user->role_id, array("company_id=" => company_id())) }}
                                    </select>
                                    <a href="{{ route('roles.create') }}" data-reload="false"
                                        data-title="{{ _lang('Add User Role') }}"
                                        class="ajax-modal-2 select2-add d-flex align-items-center pl-3 pr-3"><i 
                                        class="ti-plus fs-16"></i></a>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-xl-3 col-form-label">{{ _lang('N° DUI') }}</label>
                                <div class="col-xl-9">
                                    <input type="text" class="form-control" name="dui" value="{{ $user->dui }}" placeholder="00000000-0"
                                        required>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-xl-3 col-form-label">{{ _lang('Código Vendedor') }}</label>
                                <div class="col-xl-9">
                                    <input type="text" class="form-control" name="seller_code" value="{{ $user->seller_code }}" maxlength="30"
                                        required>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-xl-3 col-form-label">{{ _lang('Status') }}</label>
                                <div class="col-xl-9">
                                    <select class="form-control auto-select" data-selected="{{ $user->status }}"
                                        name="status" required>
                                        <option value="">{{ _lang('Select One') }}</option>
                                        <option value="1">{{ _lang('Active') }}</option>
                                        <option value="0">{{ _lang('In Active') }}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-xl-3 col-form-label">{{ _lang('Profile Picture') }}</label>
                                <div class="col-xl-9">
                                    <input type="file" class="form-control dropify" name="profile_picture" data-allowed-file-extensions="png jpg jpeg PNG JPG JPEG" data-default-file="{{ profile_picture($user->profile_picture) }}">
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-xl-9 offset-xl-3">
                                    <button type="submit" class="btn btn-primary">{{ _lang('Update User') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>    
            </div>
        </div>
    </div>
</div>

@endsection

@section('js-script')
    <script src="{{ asset('public/backend/plugins/inputmask/inputmask.min.js') }}"></script>
    <script>
        $('input[name="dui"]').inputmask('99999999-9', {placeholder: '00000000-0'});
    </script>
@endsection