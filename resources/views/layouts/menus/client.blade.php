<div class="sb-sidenav-menu-heading">{{ _lang('NAVIGATIONS') }}</div>

<a class="nav-link" href="{{ route('dashboard') }}">
    <div class="sb-nav-link-icon"><i class="ti-dashboard"></i></div>
    {{ _lang('Dashboard') }}
</a>

<a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#invoices" aria-expanded="false"
    aria-controls="collapseLayouts">
    <div class="sb-nav-link-icon"><i class="ti-receipt"></i></div>
    {{ _lang('Invoices') }}
    <div class="sb-sidenav-collapse-arrow"><i class="ti-angle-down"></i></div>
</a>
<div class="collapse" id="invoices" aria-labelledby="headingOne" data-parent="#sidenavAccordion">
    <nav class="sb-sidenav-menu-nested nav">
        <a class="nav-link" href="{{ route('client.invoices') }}">{{ _lang('All Invoices') }}</a>
		<a class="nav-link" href="{{ route('client.invoices','Unpaid') }}">{{ _lang('Unpaid Invoices') }}</a>
		<a class="nav-link" href="{{ route('client.invoices','Paid') }}">{{ _lang('Paid Invoices') }}</a>
		<a class="nav-link" href="{{ route('client.invoices','Partially_Paid') }}">{{ _lang('Partially Paid Invoices') }}</a>
		<a class="nav-link" href="{{ route('client.invoices','Canceled') }}">{{ _lang('Canceled Invoices') }}</a>
    </nav>
</div>

<a class="nav-link" href="{{ route('client.quotations') }}">
    <div class="sb-nav-link-icon"><i class="ti-file"></i></div>
    {{ _lang('Quotations') }}
</a>

<a class="nav-link" href="{{ route('client.transactions') }}">
    <div class="sb-nav-link-icon"><i class="ti-credit-card"></i></div>
    {{ _lang('Transactions') }}
</a>