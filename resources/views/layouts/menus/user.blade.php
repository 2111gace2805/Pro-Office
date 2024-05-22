@php $permissions = permission_list(); @endphp

<div class="sb-sidenav-menu-heading">{{ _lang('NAVIGATIONS') }}</div>

<a class="nav-link" href="{{ route('dashboard') }}"><div class="sb-nav-link-icon"><i class="ti-dashboard"></i></div>{{ _lang('Dashboard') }}</a>

<a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#contacts" aria-expanded="false"
    aria-controls="collapseLayouts">
    <div class="sb-nav-link-icon"><i class="ti-user"></i></div>
    {{ _lang('Contacts') }}
    <div class="sb-sidenav-collapse-arrow"><i class="ti-angle-down"></i></div>
</a>
<div class="collapse" id="contacts" aria-labelledby="headingOne" data-parent="#sidenavAccordion">
    <nav class="sb-sidenav-menu-nested nav">
        @if(in_array('contacts.index', $permissions)) <a class="nav-link" href="{{ route('contacts.index') }}">{{ _lang('Contacts List') }}</a> @endif
        @if(in_array('contacts.create', $permissions)) <a class="nav-link" href="{{ route('contacts.create') }}">{{ _lang('Add New') }}</a> @endif
        @if(in_array('contact_groups.index', $permissions)) <a class="nav-link" href="{{ route('contact_groups.index') }}">{{ _lang('Contact Group') }}</a> @endif
    </nav>
</div>


<a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#inventories" aria-expanded="false"
    aria-controls="collapseLayouts">
    <div class="sb-nav-link-icon"><i class="ti-shopping-cart"></i></div>
    {{ _lang('Inventories') }}
    <div class="sb-sidenav-collapse-arrow"><i class="ti-angle-down"></i></div>
</a>
<div class="collapse" id="inventories" aria-labelledby="headingOne" data-parent="#sidenavAccordion">
    <nav class="sb-sidenav-menu-nested nav" id="navAccordionInventories">
            @if(in_array('products.index', $permissions)) <a class="nav-link" href="{{ route('products.index') }}">{{ _lang('Products') }}</a> @endif
            @if(in_array('categories.index', $permissions)) <a class="nav-link" href="{{ route('categories.index') }}">{{ _lang('Categories') }}</a> @endif
            @if(in_array('brands.index', $permissions)) <a class="nav-link" href="{{ route('brands.index') }}">{{ _lang('Brands') }}</a> @endif
            @if(in_array('product_group.index', $permissions)) <a class="nav-link" href="{{ route('product_group.index') }}">{{ _lang('Product Groups') }}</a> @endif
            @if(in_array('codigo_arancelarios.index', $permissions)) <a class="nav-link" href="{{ route('codigo_arancelarios.index') }}">{{ _lang('Códigos arancelarios') }}</a> @endif

            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#tranfer-items" aria-expanded="false" aria-controls="collapseLayouts">
                <div class="sb-nav-link-icon"><i class="ti-exchange-vertical"></i></div>
                {{ _lang('Tranfers') }}
                <div class="sb-sidenav-collapse-arrow"><i class="ti-angle-down"></i></div>
            </a>
            <div class="collapse" id="tranfer-items" aria-labelledby="headingOne" data-parent="#navAccordionInventories">
                <nav class="sb-sidenav-menu-nested nav">
                    @if(in_array('passes.create', $permissions)) <a class="nav-link" href="{{ route('passes.create') }}">{{ _lang('Add New') }}</a> @endif
                    @if(in_array('passes.index', $permissions)) <a class="nav-link" href="{{ route('passes.index', [1]) }}">{{ _lang('Transfers') }}</a> @endif
                    @if(in_array('passes.index', $permissions)) <a class="nav-link" href="{{ route('passes.index', [0]) }}">{{ _lang('Transfers Received') }}</a> @endif
                </nav>
            </div>
    </nav>
</div>



@if(in_array('companies.index', $permissions)) <a class="nav-link" href="{{ route('companies.index') }}"><div class="sb-nav-link-icon"><i class="ti-layout-grid2"></i></div>{{ _lang('Companies') }}</a> @endif

@if(in_array('services.index', $permissions)) <a class="nav-link" href="{{ route('services.index') }}"><div class="sb-nav-link-icon"><i class="ti-agenda"></i></div>{{ _lang('Services') }}</a> @endif

<a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#suppliers" aria-expanded="false"
    aria-controls="collapseLayouts">
    <div class="sb-nav-link-icon"><i class="ti-truck"></i></div>
    {{ _lang('Supplier') }}
    <div class="sb-sidenav-collapse-arrow"><i class="ti-angle-down"></i></div>
</a>
<div class="collapse" id="suppliers" aria-labelledby="headingOne" data-parent="#sidenavAccordion">
    <nav class="sb-sidenav-menu-nested nav">
        @if(in_array('suppliers.index', $permissions)) <a class="nav-link" href="{{ route('suppliers.index') }}">{{ _lang('Supplier List') }}</a> @endif
        @if(in_array('suppliers.create', $permissions)) <a class="nav-link" href="{{ route('suppliers.create') }}">{{ _lang('Add New') }}</a> @endif
    </nav>
</div>

<a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#purchase_orders" aria-expanded="false"
    aria-controls="collapseLayouts">
    <div class="sb-nav-link-icon"><i class="ti-bag"></i></div>
    {{ _lang('Purchase') }}
    <div class="sb-sidenav-collapse-arrow"><i class="ti-angle-down"></i></div>
</a>
<div class="collapse" id="purchase_orders" aria-labelledby="headingOne" data-parent="#sidenavAccordion">
    <nav class="sb-sidenav-menu-nested nav">
        @if(in_array('purchase_orders.index', $permissions)) <a class="nav-link" href="{{ route('purchase_orders.index') }}">{{ _lang('Purchase Orders') }}</a> @endif
        @if(in_array('purchase_orders.create', $permissions)) <a class="nav-link" href="{{ route('purchase_orders.create') }}">{{ _lang('New Purchase Order') }}</a> @endif
        @if(in_array('purchase_returns.index', $permissions)) <a class="nav-link" href="{{ route('purchase_returns.index') }}">{{ _lang('Purchase Return') }}</a> @endif
    </nav>
</div>

<a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#sales" aria-expanded="false"
    aria-controls="collapseLayouts">
    <div class="sb-nav-link-icon"><i class="ti-shopping-cart-full"></i></div>
    {{ _lang('Sales') }}
    <div class="sb-sidenav-collapse-arrow"><i class="ti-angle-down"></i></div>
</a>
<div class="collapse" id="sales" aria-labelledby="headingOne" data-parent="#sidenavAccordion">
    <nav class="sb-sidenav-menu-nested nav">
        @if(in_array('invoices.create', $permissions)) <a class="nav-link" href="{{ route('invoices.create') }}">{{ _lang('Create Invoice') }}</a> @endif
        @if(in_array('invoices.index', $permissions)) <a class="nav-link" href="{{ route('invoices.index') }}">{{ _lang('Invoice List') }}</a> @endif
        {{-- @if(in_array('quotations.create', $permissions)) <a class="nav-link" href="{{ route('quotations.create') }}">{{ _lang('Create Quotation') }}</a> @endif
        @if(in_array('quotations.index', $permissions)) <a class="nav-link" href="{{ route('quotations.index') }}">{{ _lang('Quotation List') }}</a> @endif --}}
        @if(in_array('sales_returns.index', $permissions)) <a class="nav-link" href="{{ route('sales_returns.index') }}">{{ _lang('Sales Return') }}</a> @endif
    </nav>
</div>





<a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#treasury" aria-expanded="false"
    aria-controls="collapseLayouts">
    <div class="sb-nav-link-icon"><i class="ti-server"></i></div>
    {{ _lang('Treasury') }}
    <div class="sb-sidenav-collapse-arrow"><i class="ti-angle-down"></i></div>
</a>
<div class="collapse" id="treasury" aria-labelledby="headingOne" data-parent="#sidenavAccordion">
    <nav class="sb-sidenav-menu-nested nav" id="navAccordionTreasury">
                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#accounts" aria-expanded="false" aria-controls="collapseLayouts">
                <div class="sb-nav-link-icon"><i class="ti-credit-card"></i></div>
                {{ _lang('Accounts') }}
                <div class="sb-sidenav-collapse-arrow"><i class="ti-angle-down"></i></div>
                </a>
                <div class="collapse" id="accounts" aria-labelledby="headingOne" data-parent="#navAccordionTreasury">
                    <nav class="sb-sidenav-menu-nested nav">
                        @if(in_array('accounts.index', $permissions)) <a class="nav-link" href="{{ route('accounts.index') }}">{{ _lang('All Account') }}</a> @endif
                        @if(in_array('accounts.create', $permissions)) <a class="nav-link" href="{{ route('accounts.create') }}">{{ _lang('Add New Account') }}</a> @endif
                    </nav>
                </div>

                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#transactions" aria-expanded="false" aria-controls="collapseLayouts">
                <div class="sb-nav-link-icon"><i class="ti-receipt"></i></div>
                {{ _lang('Transactions') }}
                <div class="sb-sidenav-collapse-arrow"><i class="ti-angle-down"></i></div>
                </a>
                <div class="collapse" id="transactions" aria-labelledby="headingOne" data-parent="#navAccordionTreasury">
                    <nav class="sb-sidenav-menu-nested nav">
                        @if(in_array('income.index', $permissions)) <a class="nav-link" href="{{ route('income.index') }}">{{ _lang('Income/Deposit') }}</a> @endif
                        @if(in_array('expense.index', $permissions)) <a class="nav-link" href="{{ route('expense.index') }}">{{ _lang('Expense') }}</a> @endif
                        @if(in_array('transfer.create', $permissions)) <a class="nav-link" href="{{ route('transfer.create') }}">{{ _lang('Transfer') }}</a> @endif
                        @if(in_array('income.income_calendar', $permissions)) <a class="nav-link" href="{{ route('income.income_calendar') }}">{{ _lang('Income Calendar') }}</a> @endif
                        @if(in_array('expense.expense_calendar', $permissions)) <a class="nav-link" href="{{ route('expense.expense_calendar') }}">{{ _lang('Expense Calendar') }}</a> @endif
                    </nav>
                </div>

                <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#recurring_transaction" aria-expanded="false" aria-controls="collapseLayouts">
                <div class="sb-nav-link-icon"><i class="ti-wallet"></i></div>
                {{ _lang('Recurring Transaction') }}
                <div class="sb-sidenav-collapse-arrow"><i class="ti-angle-down"></i></div>
                </a>
                <div class="collapse" id="recurring_transaction" aria-labelledby="headingOne" data-parent="#navAccordionTreasury">
                    <nav class="sb-sidenav-menu-nested nav">
                        @if(in_array('repeating_income.create', $permissions)) <a class="nav-link" href="{{ route('repeating_income.create') }}">{{ _lang('Add Repeating Income') }}</a> @endif
                        @if(in_array('repeating_income.index', $permissions)) <a class="nav-link" href="{{ route('repeating_income.index') }}">{{ _lang('Repeating Income List') }}</a> @endif
                        @if(in_array('repeating_expense.create', $permissions)) <a class="nav-link" href="{{ route('repeating_expense.create') }}">{{ _lang('Add Repeating Expense') }}</a> @endif
                        @if(in_array('repeating_expense.index', $permissions)) <a class="nav-link" href="{{ route('repeating_expense.index') }}">{{ _lang('Repeating Expense List') }}</a> @endif
                    </nav>
                </div>
    </nav>
</div>


<a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#reports" aria-expanded="false"
    aria-controls="collapseLayouts">
    <div class="sb-nav-link-icon"><i class="ti-bar-chart"></i></div>
    {{ _lang('Reports') }}
    <div class="sb-sidenav-collapse-arrow"><i class="ti-angle-down"></i></div>
</a>
<div class="collapse" id="reports" aria-labelledby="headingOne" data-parent="#sidenavAccordion">
    <nav class="sb-sidenav-menu-nested nav">
      @if(in_array('reports.account_statement', $permissions)) <a class="nav-link" href="{{ route('reports.account_statement') }}">{{ _lang('Account Statement') }}</a> @endif
	  @if(in_array('reports.income_report', $permissions)) <a class="nav-link" href="{{ route('reports.income_report') }}">{{ _lang('Income Report') }}</a> @endif
	  @if(in_array('reports.expense_report', $permissions)) <a class="nav-link" href="{{ route('reports.expense_report') }}">{{ _lang('Expense Report') }}</a> @endif
	  @if(in_array('reports.transfer_report', $permissions)) <a class="nav-link" href="{{ route('reports.transfer_report') }}">{{ _lang('Transfer Report') }}</a> @endif
	  @if(in_array('reports.income_vs_expense', $permissions)) <a class="nav-link" href="{{ route('reports.income_vs_expense') }}">{{ _lang('Income VS Expense') }}</a> @endif
	  @if(in_array('reports.report_by_payer', $permissions)) <a class="nav-link" href="{{ route('reports.report_by_payer') }}">{{ _lang('Report by Payer') }}</a> @endif
	  @if(in_array('reports.report_by_payee', $permissions)) <a class="nav-link" href="{{ route('reports.report_by_payee') }}">{{ _lang('Report by Payee') }}</a> @endif
    </nav>
</div>



<div class="sb-sidenav-menu-heading">{{ _lang('System Settings') }}</div>

<a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#company_settings" aria-expanded="false"
    aria-controls="collapseLayouts">
    <div class="sb-nav-link-icon"><i class="ti-panel"></i></div>
    {{ _lang('System Settings') }}
    <div class="sb-sidenav-collapse-arrow"><i class="ti-angle-down"></i></div>
</a>
<div class="collapse" id="company_settings" aria-labelledby="headingOne" data-parent="#sidenavAccordion">
    <nav class="sb-sidenav-menu-nested nav">
        @if(in_array('settings.update_settings', $permissions)) <a class="nav-link" href="{{ route('settings.update_settings') }}">{{ _lang('General Settings') }}</a> @endif
        @if(in_array('product_units.index', $permissions)) <a class="nav-link" href="{{ route('product_units.index') }}">{{ _lang('Product Unit') }}</a> @endif
        @if(in_array('email_template.index', $permissions)) <a class="nav-link" href="{{ route('email_template.index') }}">{{ _lang('Email Template') }}</a> @endif
        @if(in_array('database_backups.list', $permissions)) <a class="nav-link" href="{{ route('database_backups.list') }}">{{ _lang('Database Backup') }}</a> @endif
    </nav>
</div>

<a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#staffs" aria-expanded="false"
    aria-controls="collapseLayouts">
    <div class="sb-nav-link-icon"><i class="ti-user"></i></div>
    {{ _lang('User Management') }}
    <div class="sb-sidenav-collapse-arrow"><i class="ti-angle-down"></i></div>
</a>
<div class="collapse" id="staffs" aria-labelledby="headingOne" data-parent="#sidenavAccordion">
    <nav class="sb-sidenav-menu-nested nav">
        @if(in_array('users.index', $permissions)) <a class="nav-link" href="{{ route('users.index') }}">{{ _lang('All User') }}</a> @endif
        @if(in_array('users.create', $permissions)) <a class="nav-link" href="{{ route('users.create') }}">{{ _lang('Add New') }}</a> @endif
        @if(in_array('roles.index', $permissions)) <a class="nav-link" href="{{ route('roles.index') }}">{{ _lang('User Roles') }}</a> @endif
        @if(in_array('permission.index', $permissions)) <a class="nav-link" href="{{ route('permission.index') }}">{{ _lang('Access Control') }}</a> @endif
    </nav>
</div>

<a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#languages" aria-expanded="false"
    aria-controls="collapseLayouts">
    <div class="sb-nav-link-icon"><i class="ti-world"></i></div>
    {{ _lang('Languages') }}
    <div class="sb-sidenav-collapse-arrow"><i class="ti-angle-down"></i></div>
</a>
<div class="collapse" id="languages" aria-labelledby="headingOne" data-parent="#sidenavAccordion">
    <nav class="sb-sidenav-menu-nested nav">
        @if(in_array('languages.index', $permissions)) <a class="nav-link" href="{{ route('languages.index') }}">{{ _lang('All Language') }}</a> @endif
        @if(in_array('languages.create', $permissions)) <a class="nav-link" href="{{ route('languages.create') }}">{{ _lang('Add New') }}</a> @endif
    </nav>
</div>

<a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#transaction_settings" aria-expanded="false"
    aria-controls="collapseLayouts">
    <div class="sb-nav-link-icon"><i class="ti-credit-card"></i></div>
    {{ _lang('Transaction Settings') }}
    <div class="sb-sidenav-collapse-arrow"><i class="ti-angle-down"></i></div>
</a>
<div class="collapse" id="transaction_settings" aria-labelledby="headingOne" data-parent="#sidenavAccordion">
    <nav class="sb-sidenav-menu-nested nav">
        @if(in_array('chart_of_accounts.index', $permissions)) <a class="nav-link" href="{{ route('chart_of_accounts.index') }}">{{ _lang('Income & Expense Types') }}</a> @endif
        @if(in_array('payment_methods.index', $permissions)) <a class="nav-link" href="{{ route('payment_methods.index') }}">{{ _lang('Payment Methods') }}</a> @endif
        @if(in_array('taxs.index', $permissions)) <a class="nav-link" href="{{ route('taxs.index') }}">{{ _lang('Tax Settings') }}</a> @endif
    </nav>
</div>