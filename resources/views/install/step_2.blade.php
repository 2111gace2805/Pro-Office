@extends('install.layout')

@section('content')

<div class="card">
    <div class="card-header text-center">
        <h4 class="header-title m-0 p-0">Database Settings</h4>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                @if (\Session::has('error'))
                <div class="alert alert-danger">
                    <span>{{ \Session::get('error') }}</span>
                </div>
                <br />
                @endif
                <form action="{{ url('install/process_install') }}" method="post" autocomplete="off">
                    {{ csrf_field() }}
                    <div class="form-group">
                        <label>Hostname:</label>
                        <input type="text" class="form-control" value="localhost" name="hostname" id="hostname">
                    </div>

                    <div class="form-group">
                        <label>Database:</label>
                        <input type="text" class="form-control" name="database" id="database">
                    </div>

                    <div class="form-group">
                        <label>Username:</label>
                        <input type="text" class="form-control" name="username" id="username">
                    </div>

                    <div class="form-group">
                        <label>Password:</label>
                        <input type="password" class="form-control" name="password">
                    </div>
                    <button type="submit" id="next-button" class="btn btn-install">Next</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js-script')
<script>
(function($) {
    "use strict";

    $('#next-button').attr('disabled', true);

    $('#hostname, #username, #database').keyup(function() {
        inputCheck();
    });

})(jQuery);

function inputCheck() {
    hostname = $('#hostname').val();
    username = $('#username').val();
    database = $('#database').val();

    if (hostname != '' && username != '' && database != '') {
        $('#next-button').attr('disabled', false);
    } else {
        $('#next-button').attr('disabled', true);
    }
}
</script>
@endsection