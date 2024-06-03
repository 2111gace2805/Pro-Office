<?php

use App\Http\Controllers\ActivoController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CashController;
use App\Http\Controllers\CashMovementController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DepreciationController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\PassController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Maintenance;
use FontLib\Table\Type\name;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderNoteController;
use App\Http\Controllers\KitController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
 */

Route::group(['middleware' => ['install']], function () {

    Route::get('/', function () {
        return redirect('login');
    });

    Auth::routes();
    Route::get('/logout', 'Auth\LoginController@logout');

    Route::group(['middleware' => ['auth']], function () {

        Route::get('dashboard', 'DashboardController@index')->name('dashboard');

        //Profile Controller
        Route::get('profile', 'ProfileController@index')->name('profile.index');
        Route::get('profile/edit', 'ProfileController@edit')->name('profile.edit');
        Route::post('profile/update', 'ProfileController@update')->name('profile.update')->middleware('demo');
        Route::get('profile/change_password', 'ProfileController@change_password')->name('profile.change_password');
        Route::post('profile/update_password', 'ProfileController@update_password')->name('profile.update_password')->middleware('demo');

        /** Admin Only Route **/
        Route::group(['middleware' => ['admin', 'demo'], 'prefix' => 'admin'], function () {

            //User Management
            Route::resource('users', 'UserController');

            //Language Controller
            Route::resource('languages', 'LanguageController');

            //Utility Controller
            Route::match(['get', 'post'], 'administration/general_settings/{store?}', 'UtilityController@settings')->name('settings.update_settings');
            Route::post('administration/upload_logo', 'UtilityController@upload_logo')->name('settings.uplaod_logo');
            Route::get('administration/database_backup_list', 'UtilityController@database_backup_list')->name('database_backups.list');
            Route::get('administration/create_database_backup', 'UtilityController@create_database_backup')->name('database_backups.create');
            Route::delete('administration/destroy_database_backup/{id}', 'UtilityController@destroy_database_backup');
            Route::get('administration/download_database_backup/{id}', 'UtilityController@download_database_backup')->name('database_backups.download');
            Route::post('administration/remove_cache', 'UtilityController@remove_cache')->name('settings.remove_cache');

        });

        /** Dynamic Permission **/
        Route::group(['middleware' => ['permission']], function () {

			//Dashboard Permissions
			Route::get('dashboard/current_day_income', 'DashboardController@current_day_income')->name('dashboard.current_day_income');
			Route::get('dashboard/current_day_expense', 'DashboardController@current_day_expense')->name('dashboard.current_day_expense');
			Route::get('dashboard/current_month_income', 'DashboardController@current_month_income')->name('dashboard.current_month_income');
			Route::get('dashboard/current_month_expense', 'DashboardController@current_month_expense')->name('dashboard.current_month_expense');
			Route::get('dashboard/yearly_income_vs_expense', 'DashboardController@yearly_income_vs_expense')->name('dashboard.yearly_income_vs_expense');
			Route::get('dashboard/latest_income', 'DashboardController@latest_income')->name('dashboard.latest_income');
			Route::get('dashboard/latest_expense', 'DashboardController@latest_expense')->name('dashboard.latest_expense');
			Route::get('dashboard/monthly_income_vs_expense', 'DashboardController@monthly_income_vs_expense')->name('dashboard.monthly_income_vs_expense');
			Route::get('dashboard/financial_account_balance', 'DashboardController@financial_account_balance')->name('dashboard.financial_account_balance');

            //Contact Group
			Route::resource('contact_groups','ContactGroupController');

			//ruta de bitacora
			Route::get('/logs', [LogController::class, 'index'])->name('log.index');
		     
			//Contact Controller
			Route::get('contacts/get_table_data','ContactController@get_table_data');
			Route::post('contacts/send_email/{id}','ContactController@send_email')->name('contacts.send_email');
			Route::get('contacts/load','ContactController@cargarClientes')->name('contacts.load');
			Route::post('contacts/load_excel','ContactController@cargarClientesExcel')->name('contacts.load_excel');
			Route::post('contacts/verifyClientExist','ContactController@verifyClientExist');
			Route::resource('contacts','ContactController');
			Route::get('contacts/get_contact/{id}', [ContactController::class, 'get_contact']);

			//Account Controller
			Route::resource('accounts','AccountController');
			
			//Income Controller
			Route::get('income/get_table_data','IncomeController@get_table_data');
			Route::get('income/calendar','IncomeController@calendar')->name('income.income_calendar');
			Route::resource('income','IncomeController');
			
			//Expense Controller
			Route::get('expense/get_table_data','ExpenseController@get_table_data');
			Route::get('expense/calendar','ExpenseController@calendar')->name('expense.expense_calendar');
			Route::resource('expense','ExpenseController');
			
			//Transfer Controller
			Route::get('transfer/create', 'TransferController@create')->name('transfer.create');
			Route::post('transfer/store', 'TransferController@store')->name('transfer.store');
			
			//Repeating Income
			Route::get('repeating_income/get_table_data','RepeatingIncomeController@get_table_data');
			Route::resource('repeating_income','RepeatingIncomeController');
			
			//Repeating Expense
			Route::get('repeating_expense/get_table_data','RepeatingExpenseController@get_table_data');
			Route::resource('repeating_expense','RepeatingExpenseController');

			//Chart Of Accounts
			Route::resource('chart_of_accounts','ChartOfAccountController')->except('show');

			//Payment Method
			Route::resource('payment_methods','PaymentMethodController')->except('show');
					
			//Supplier Controller
			Route::resource('suppliers','SupplierController');

			//Product Controller
			Route::get('products/get_product/{id}','ProductController@get_product');
			Route::get('products/load','ProductController@cargarProductos')->name('products.load');
			Route::post('products/load_excel','ProductController@cargarProductosExcel')->name('products.load_excel');
			Route::post('products/get_table_data','ProductController@get_table_data');
			Route::resource('products','ProductController');

			//Product Controller
			Route::resource('services','ServiceController');

			//Purchase Order
			Route::match(['get', 'post'],'purchase_orders/store_payment/{id?}','PurchaseController@store_payment')->name('purchase_orders.create_payment');
			Route::get('purchase_orders/view_payment/{id}','PurchaseController@view_payment')->name('purchase_orders.view_payment');
			Route::get('purchase_orders/download_pdf/{id}','PurchaseController@download_pdf')->name('purchase_orders.download_pdf');
			Route::post('purchase_orders/get_table_data','PurchaseController@get_table_data');
			Route::resource('purchase_orders','PurchaseController');
			Route::get('purchase_orders/editOrder/{id}', [PurchaseController::class, 'editOrder'])->name('purchase_orders.editOrder');
			Route::put('purchase_orders/updateOrder/{id}', [PurchaseController::class, 'updateOrder'])->name('purchase_orders.updateOrder');

			//Purchase Return
			Route::get('purchase_returns/get_table_data','PurchaseReturnController@get_table_data');
			Route::resource('purchase_returns','PurchaseReturnController');
			
			//Sales Return
			Route::get('sales_returns/get_table_data','SalesReturnController@get_table_data');
			Route::get('sales_returns/get_invoice_item','SalesReturnController@getInvoiceItem');
			Route::resource('sales_returns','SalesReturnController');

			//Order notes
			Route::post('order_notes/get_table_data','OrderNoteController@get_table_data');
			Route::get('order_notes/print/{id}','OrderNoteController@print');
			Route::post('order_notes/updateStatus','OrderNoteController@updateStatus');
			Route::post('order_notes/cancelNote','OrderNoteController@cancelNote');
			Route::resource('order_notes','OrderNoteController');

			//KITS
			Route::post('kits/get_table_data','KitController@get_table_data');
			Route::resource('kits','KitController');

			//Cash Controller
			Route::get('cash/get_table_data','CashController@get_table_data');
			Route::resource('cash','CashController');
			
			Route::get('invoices/verificarCajaAbierta','InvoiceController@verificarCajaAbierta');

			//Invoice Controller
			Route::get('invoices/download_pdf/{id}','InvoiceController@download_pdf')->name('invoices.download_pdf');
			Route::match(['get', 'post'],'invoices/store_payment/{invoice_id?}','InvoiceController@store_payment')->name('invoices.create_payment');
			Route::get('invoices/view_payment/{id}','InvoiceController@view_payment')->name('invoices.view_payment');
			Route::match(['get', 'post'],'invoices/send_email/{invoice_id?}','InvoiceController@send_email')->name('invoices.send_email');			
			Route::post('invoices/get_table_data','InvoiceController@get_table_data');
			Route::post('invoices/obtenerSelloHacienda','InvoiceController@obtenerSelloHacienda');
			Route::resource('invoices','InvoiceController');
			Route::get('invoices/get-number/{tipodoc_id}', fn($tipodoc_id)=>get_invoice_number($tipodoc_id));
			Route::get('invoices/download_anexo_descargo/{id}','InvoiceController@download_anexo_descargo')->name('invoices.anexo_descargo');

			//Quotation Controller
			Route::get('quotations/download_pdf/{id}','QuotationController@download_pdf')->name('quotations.download_pdf');
			Route::get('quotations/convert_invoice/{quotation_id?}','QuotationController@convert_invoice')->name('quotations.convert_invoice');
			Route::match(['get', 'post'],'quotations/send_email/{quotation_id?}','QuotationController@send_email')->name('quotations.send_email');
			Route::get('quotations/get_table_data','QuotationController@get_table_data');
			Route::resource('quotations','QuotationController');
			// Route::resource('quotations','QuotationController')->except('store');
			Route::get('quotations/download_example/{id}','QuotationController@download_example')->name('quotations.download_example');
			Route::get('quotations/export_to_excel/{id}','QuotationController@exportToExcel')->name('quotations.export_to_excel');
			// Route::post('quotations/store','QuotationController@store')->name('quotations.store');
			
			//Company Email Template
			Route::get('company_email_template/get_template/{id}','CompanyEmailTemplateController@get_template');
			Route::resource('email_template','CompanyEmailTemplateController');
			
			//Tax Controller
			Route::resource('taxs','TaxController')->except('show');
			
			//Product Unit Controller
			Route::resource('product_units','ProductUnitController')->except('show');

			//Controlador de categorias
			Route::resource('categories','CategoryController')->except('show');

			//Controlador de transferencias de bodegas
			Route::resource('passes','PassController')->except(['show', 'index']);
			Route::get('/transfer-detail/{transfer}/{send}', [PassController::class, 'show'])->name('transfer.show');
			Route::get('/transfer-list/{send}', [PassController::class, 'index'])->name('passes.index');
			Route::get('/transfer_receive/{send}', [PassController::class, 'received'])->name('list-received');
			Route::get('/new-incoming-transfer', [PassController::class, 'incoming'])->name('passes.incoming');
			Route::post('/recieved-items', [PassController::class, 'ItemsReceived'])->name('recieved-items');
			Route::post('/guardar-transaferencia', [PassController::class, 'SaveItemsReceived'])->name('passes.recieved');

			//Company Controller
			Route::resource('companies','CompanyController')->except('show');
			Route::get('/change-company/{company}', [CompanyController::class, 'change'])->name('companies.change');
			
			//Report Controller
			Route::match(['get', 'post'],'reports/account_statement/{view?}', 'ReportController@account_statement')->name('reports.account_statement');
			Route::match(['get', 'post'],'reports/income_report/{view?}', 'ReportController@income_report')->name('reports.income_report');
			Route::match(['get', 'post'],'reports/expense_report/{view?}', 'ReportController@expense_report')->name('reports.expense_report');
			Route::match(['get', 'post'],'reports/transfer_report/{view?}', 'ReportController@transfer_report')->name('reports.transfer_report');
			Route::match(['get', 'post'],'reports/income_vs_expense/{view?}', 'ReportController@income_vs_expense')->name('reports.income_vs_expense');
			Route::match(['get', 'post'],'reports/report_by_payer/{view?}', 'ReportController@report_by_payer')->name('reports.report_by_payer');
			Route::match(['get', 'post'],'reports/report_by_payee/{view?}', 'ReportController@report_by_payee')->name('reports.report_by_payee');

            //Staff Roles
            Route::resource('roles', 'RoleController');

            //Permission Controller
            Route::get('permission/control/{user_id?}', 'PermissionController@index')->name('permission.index');
            Route::post('permission/store', 'PermissionController@store')->name('permission.store');

			//Controlador de marcas
			Route::resource('brands', 'BrandController')->except('show');
			Route::resource('codigo_arancelarios', 'CodigoArancelarioController')->except('show');
			
			//Controlador de product groups
			Route::resource('product_group', 'ProductGroupController')->except('show');

			//Cash movement
			Route::post('cash_movement/get_table_data','CashMovementController@get_table_data');
			Route::resource('cash_movement','CashMovementController');
			Route::post('cash_movement/get_table_data_closing_cash', [CashMovementController::class, 'get_table_data_closing_cash']);
			Route::get('/cash_movement_get_resumen_ventas', [CashMovementController::class, 'get_resumen_ventas']);
			Route::post('cash_movement/get_table_data_closing_cash_invoices', [CashMovementController::class, 'get_table_data_closing_cash_invoices']);
        });

		Route::group(['middleware' => ['client']], function () {
		    Route::get('client/invoices/{status?}','ClientController@invoices')->name('client.invoices');
		    Route::get('client/quotations','ClientController@quotations')->name('client.quotations');
		    Route::get('client/transactions','ClientController@transactions')->name('client.transactions');
			Route::get('client/view_transaction/{id}','ClientController@view_transaction')->name('client.view_transaction');	
		});

		// Rutas de activos fijos
		Route::get('lista-activos', [ActivoController::class, 'index'])->name('activos.index');
		Route::get('/assets/create-modal', [ActivoController::class, 'createModal'])->name('assets.createModal');
		Route::get('/typeactive', [ActivoController::class, 'tipoActivo'])->name('type-active');
		Route::post('/assets', [ActivoController::class, 'store'])->name('assets.store');
		Route::get('/asset/{id}', [ActivoController::class, 'show'])->name('asset.show');
		Route::get('/asset/{id}/edit', [ActivoController::class, 'edit'])->name('asset.edit');
		Route::put('/asset/{id}', [ActivoController::class, 'update'])->name('asset.update');
		Route::put('/asset/softdelete/{id}', [ActivoController::class, 'softDelete'])->name('asset.softdelete');
		Route::get('activos-debaja', [ActivoController::class, 'deBaja'])->name('activos.deBaja');
		Route::get('/asset-types/create', [ActivoController::class, 'createTipoActivo'])->name('asset-types.create');
		Route::post('/asset-types/store', [ActivoController::class, 'storeTipoActivo'])->name('asset-types.store');

		// Rutas de mantenimientos
		Route::get('lista-mantenimiento', [MaintenanceController::class, 'index'])->name('lista-mantenimiento');
		Route::get('maintenance/create', [MaintenanceController::class, 'create'])->name('maintenance.create');
		Route::post('maintenance/store', [MaintenanceController::class, 'store'])->name('maintenance.store');
		Route::get('maintenance/{id}/edit', [MaintenanceController::class, 'edit'])->name('maintenance.edit');
		Route::put('update-maintenance/{id}', [MaintenanceController::class, 'update'])->name('maintenance.update');
		Route::delete('delete-maintenance/{id}', [MaintenanceController::class, 'destroy'])->name('delete.maintenance');

		// Rutas de depreciaciones
		Route::get('lista-depreciaciones', [DepreciationController::class, 'index'])->name('depreciation.index');
		Route::post('save-depreciation', [DepreciationController::class, 'store'])->name('guardar.depreciacion');
		Route::put('/update-depreciation/{id}', [DepreciationController::class, 'update'])->name('depreciation.update');
		Route::delete('delete-depreciation/{id}', 'DepreciationController@destroy')->name('depreciation.destroy');

    });

});

//Socila Login
Route::get('/login/{provider}', 'Auth\SocialController@redirect');
Route::get('/login/{provider}/callback', 'Auth\SocialController@callback');

//JSON data for dashboard chart
Route::get('dashboard/json_month_wise_income_expense','DashboardController@json_month_wise_income_expense')->middleware('auth');
Route::get('dashboard/json_income_vs_expense','DashboardController@json_income_vs_expense')->middleware('auth');

//View Invoice & Quotation without login
Route::get('client/view_invoice/{id}','ClientController@view_invoice')->name('client.view_invoice');
Route::get('client/download_pdf_invoice/{id}','ClientController@download_pdf_invoice')->name('client.download_pdf_invoice');
Route::get('client/view_quotation/{id}','ClientController@view_quotation')->name('client.view_quotation');
Route::get('client/download_pdf_quotation/{id}','ClientController@download_pdf_quotation')->name('client.download_pdf_quotation');

//Ajax Select2 Controller
Route::get('ajax/get_table_data', 'Select2Controller@get_table_data');
Route::get('ajax/get_table_data_ccf', 'Select2Controller@get_table_data_ccf');

//Run Cron Jobs
Route::get('console/run','CronJobsController@run');	

//Online Invoice payments
Route::get('client/paypal_payment_authorize/{paypal_order_id}/{invoice_id}','ClientController@paypal_payment_authorize');
Route::post('client/stripe_payment_authorize/{invoice_id}','ClientController@stripe_payment_authorize');

Route::get('/installation', 'Install\InstallController@index');
Route::get('install/database', 'Install\InstallController@database');
Route::post('install/process_install', 'Install\InstallController@process_install');
Route::get('install/create_user', 'Install\InstallController@create_user');
Route::post('install/store_user', 'Install\InstallController@store_user');
Route::get('install/system_settings', 'Install\InstallController@system_settings');
Route::post('install/finish', 'Install\InstallController@final_touch');

//Update System
Route::get('migration/update', 'Install\UpdateController@update_migration');

// esta ruta esta afuera por que es de prueba. deben estar protegidas por un middleware
Route::get('/test/{id}', [InvoiceController::class, 'sendInvoiceToHacienda']);

Route::get('/stock/{id}', [ProductController::class, 'stock'])->name('stock.modal');
Route::post('/save-stock/{id?}', [ProductController::class, 'saveStock'])->name('save-stock');

Route::get('/test', [InvoiceController::class, 'sendMessage']);

Route::get('download/json/{invoice}', 'InvoiceController@downloadJson')->name('download.json');
Route::get('/download-pdf/{invoice}', 'InvoiceController@downloadPdf')->name('download.pdf');

Route::get('/testCorreo/{id}', [InvoiceController::class, 'sendEmailFactura']);
Route::get('/testAnular/{id}', [InvoiceController::class, 'anularInvoiceMH']);
Route::POST('/contingenciaInvoiceMH/{id}', [InvoiceController::class, 'contingenciaInvoiceMH']);
Route::get('/enviarPruebas/{id}', [InvoiceController::class, 'enviarPruebas']);
