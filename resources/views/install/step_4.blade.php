@extends('install.layout')

@section('content')
<div class="card">
    <div class="card-header text-center">
        <h4 class="header-title m-0 p-0">System Settings</h4>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <form action="{{ url('install/finish') }}" method="post" autocomplete="off">
                    {{ csrf_field() }}

                    <div class="form-group">
                        <label class="control-label">Company Name</label>
                        <input type="text" class="form-control" name="company_name" required>
                    </div>

                    <div class="form-group">
                        <label class="control-label">Site Title</label>
                        <input type="text" class="form-control" name="site_title" required>
                    </div>

                    <div class="form-group">
                        <label class="control-label">Phone</label>
                        <input type="text" class="form-control" name="phone" required>
                    </div>

                    <div class="form-group">
                        <label class="control-label">Email</label>
                        <input type="text" class="form-control" name="email" required>
                    </div>

                    <div class="form-group">
                        <label class="control-label">Timezone</label>
                        <select class="form-control select2" name="timezone" required>
                            <option value="">Select One</option>
                            {{ create_timezone_option() }}
                        </select>
                    </div>

                    <button type="submit" id="next-button" class="btn btn-install">Finish</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection