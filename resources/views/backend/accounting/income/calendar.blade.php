@extends('layouts.app')

@section('content')
<!--calendar css-->
<link href="{{ asset('public/backend/plugins/fullcalendar/packages/core/main.css') }}" rel="stylesheet" />
<link href="{{ asset('public/backend/plugins/fullcalendar/packages/daygrid/main.css') }}" rel="stylesheet" />
<link href="{{ asset('public/backend/plugins/fullcalendar/packages/bootstrap/main.css') }}" rel="stylesheet" />
<link href="{{ asset('public/backend/plugins/fullcalendar/packages/timegrid/main.css') }}" rel="stylesheet" />
<link href="{{ asset('public/backend/plugins/fullcalendar/packages/list/main.css') }}" rel="stylesheet" />

<div class="row">
    <div class="col-md-12">
        <div class="card">
			<div class="card-header">
                <h4 class="header-title">{{ _lang('Income Calendar') }}</h4>
            </div>

            <div class="card-body">
                <div id='income_calendar'></div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js-script')
<script src="{{ asset('public/backend/plugins/fullcalendar/packages/core/main.js') }}"></script>
<script src="{{ asset('public/backend/plugins/fullcalendar/packages/daygrid/main.js') }}"></script>
<script src="{{ asset('public/backend/plugins/fullcalendar/packages/timegrid/main.js') }}"></script>
<script src="{{ asset('public/backend/plugins/fullcalendar/packages/interaction/main.js') }}"></script>
<script src="{{ asset('public/backend/plugins/fullcalendar/packages/list/main.js') }}"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('income_calendar');

    var calendar = new FullCalendar.Calendar(calendarEl, {
      plugins: [ 'interaction', 'dayGrid', 'timeGrid' ],

      header: {
        left: 'prev,next today',
        center: 'title',
		right: 'dayGridMonth, timeGridWeek, timeGridDay'
      },
      defaultView: 'dayGridMonth',
      navLinks: true, 
      editable: true,
      eventLimit: true,
	  eventBackgroundColor: "#3742fa",
	  eventBorderColor: "#3742fa",
	  timeFormat: 'h:mm',
      events: [ 
	    @php $currency = currency(); @endphp
		@foreach($transactions as $trans)
			{
				title: '{{ $trans->income_type->name." - ".decimalPlace($trans->amount, $currency) }}',
                start: '{{ $trans->getRawOriginal("trans_date") }}',
                url: '{{ action("IncomeController@show", $trans->id) }}'
			},
		@endforeach	
      ],
	  eventRender: function(info) {	
        $(info.el).addClass('ajax-modal');	  
        $(info.el).data("title","{{ _lang('View Income') }}");	  
	  },
	  eventClick: function(info) {
		info.jsEvent.preventDefault();
	  }
    });

    calendar.render();
});
</script>
@endsection