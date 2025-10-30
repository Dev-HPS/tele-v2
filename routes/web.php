<?php

use App\Http\Controllers\ApprovalBypassOutletController;
use App\Http\Controllers\ApproveOutletCallController;
use App\Http\Controllers\ApproveTransactionController;
use App\Http\Controllers\BypassOutletController;
use App\Http\Controllers\DsoOrderController;
use App\Http\Controllers\NonOrderingOutletController;
use App\Http\Controllers\OutletListController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\ComplainController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EditTransactionController;
use App\Http\Controllers\OutletCallController;
use App\Http\Controllers\OutletCallLogController;
use App\Http\Controllers\TransactionController;

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

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', [AuthController::class, 'index'])->middleware('guest')->name('login');
Route::post('authenticate', [AuthController::class, 'authenticate'])->name('authenticate');

Route::middleware(['auth', 'role:Admin,Telemarketing,Chakra,Order Handling,Kepala Non Operasional,Head Telemarketing,Leader,IT Pusat,Cs'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('outlet-call-dashboard', [DashboardController::class, 'outletCallDashboard'])->name('outlet-call-dashboard');
    Route::get('dashboard-outlet-call/{city}', [DashboardController::class, 'outletCall'])->name('dashboard-outlet-call');
    Route::post('get-outlet-call-data/{city}', [DashboardController::class, 'getOutletCallDataAjax'])->name('get-outlet-call-data');
    Route::get('get-tp-by-sbu/{sbu}', [DashboardController::class, 'getTpBySbuAjax'])->name('get-tp-by-sbu');
    Route::post('get-tp-dashboard-data', [DashboardController::class, 'getTpDashboardDataAjax'])->name('get-tp-dashboard-data');
    Route::resource('transactions', TransactionController::class);
    Route::patch('transactions/{transaction}/detail/{detail}', [TransactionController::class, 'updateDetail'])->name('transactions.update-detail');
    Route::get('cities-outlet-call/{tp}', [OutletCallController::class, 'cities'])->name('cities-outlet-call.show');
    Route::get('districts-outlet-call/{tp}/{city}', [OutletCallController::class, 'districts'])->name('districts-outlet-call.show');
    Route::get('data-outlet-call/{tp}/{city}/{district}', [OutletCallController::class, 'outlet'])->name('data-outlet-call.show');


    Route::get('cities/{residency}', [TransactionController::class, 'cities'])->name('cities.show');
    Route::get('districts/{residency}/{city}', [TransactionController::class, 'districts'])->name('districts.show');
    Route::get('outlet/{residency}/{city}/{district}', [TransactionController::class, 'outlet'])->name('outlet.index');

    Route::get('detail-outlet/{residency}/{city}/{district}/{outlet}', [TransactionController::class, 'outletById'])->name('outlet.detail');
    Route::get('product-types', [TransactionController::class, 'productTypeBySBU'])->name('product.types');
    Route::get('type/{type}/products', [TransactionController::class, 'productsByType'])->name('type.products');

    Route::get('cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('cart', [CartController::class, 'store'])->name('cart.store');
    Route::patch('cart/{cart}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('cart/{cart}', [CartController::class, 'destroy'])->name('cart.destroy');
    Route::post('cart/check-outlet-validation', [CartController::class, 'checkOutletValidation'])->name('cart.check-outlet-validation');

    // Approve Transaction
    Route::resource('approve-transactions', ApproveTransactionController::class);
    Route::put('approve-transactions/{id}/approve', [ApproveTransactionController::class, 'approve'])->name('approve-transactions.approve');
    Route::get('approve-transactions-datatable', [ApproveTransactionController::class, 'datatable'])->name('approve-transactions.datatable');

    // Outlet Call
    Route::get('outlet-call-get-sbu-options', [OutletCallController::class, 'getSbuOptions'])->name('outlet-call.get-sbu-options');
    Route::get('outlet-call-get-tp', [OutletCallController::class, 'tp'])->name('outlet-call.get-tp');
    Route::get('outlet-call-get-tp-by-sbu', [OutletCallController::class, 'getTpBySbu'])->name('outlet-call.get-tp-by-sbu');
    Route::get('outlet-call-get-tp-options', [OutletCallController::class, 'getTpOptions'])->name('outlet-call.get-tp-options');
    Route::get('outlet-call-get-cities-by-tp', [OutletCallController::class, 'getCitiesByTp'])->name('outlet-call.get-cities-by-tp');
    Route::get('outlet-call-get-districts-by-city', [OutletCallController::class, 'getDistrictsByCity'])->name('outlet-call.get-districts-by-city');
    Route::get('outlet-call-get-piutang', [OutletCallController::class, 'getPiutang'])->name('outlet-call.get-piutang');
    Route::post('outlet-call-sync', [OutletCallController::class, 'syncOutlet'])->name('outlet-call.sync');
    Route::post('outlet-call-export-pdf', [OutletCallController::class, 'exportPdf'])->name('outlet-call.export-pdf');
    Route::resource('outlet-call', OutletCallController::class);


    Route::resource('approve-outlet-call', ApproveOutletCallController::class);
    Route::post('approve-outlet-call/{id}/approve', [ApproveOutletCallController::class, 'approve'])->name('approve-outlet-call.approve');
    Route::post('approve-outlet-call/reject', [ApproveOutletCallController::class, 'reject'])->name('approve-outlet-call.reject');

    // Edit Transaction
    Route::resource('edit-transactions', EditTransactionController::class);
    Route::put('edit-outlet/{transaction}/update', [EditTransactionController::class, 'updateOutlet'])->name('edit-outlet.update');
    Route::patch('edit-detail-transactions/{transaction}/detail/{detail}', [EditTransactionController::class, 'updateDetail'])->name('edit-detail-transactions.update-detail');
    Route::delete('edit-detail-transactions/{transaction}/detail/{detail}', [EditTransactionController::class, 'deleteDetail'])->name('edit-detail-transactions.delete-detail');
    Route::get('edit-transactions-datatable', [EditTransactionController::class, 'datatable'])->name('edit-transactions.datatable');

    Route::get('outlet-cart/{outlet}', [CartController::class, 'preview'])->name('outlet.cart');
    Route::post('outlet-cart/{outlet}/checkout', [CartController::class, 'checkout'])->name('cart.checkout');

    Route::get('change-password', [UserController::class, 'changePassword'])->name('change-password');
    Route::post('change-password', [UserController::class, 'updatePassword'])->name('update-password');

    Route::get('transactions-datatable', [TransactionController::class, 'datatable'])->name('transactions.datatable');

    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
});



Route::middleware(['auth', 'role:Admin,Head Telemarketing'])->group(function () {
    Route::resource('users', UserController::class);
    Route::get('getResidence/{sbu}', [UserController::class, 'residency'])->name('getResidence');
    Route::get('users-get-tp-by-sbu/{sbu}', [UserController::class, 'getTpBySbu'])->name('users.getTpBySbu');
    Route::get('users-datatable', [UserController::class, 'datatable'])->name('users.datatable');
});

Route::middleware(['auth', 'role:Admin,Leader,Head Telemarketing,Telemarketing,IT Pusat'])->group(function () {
    Route::get('non-ordering-outlets', [NonOrderingOutletController::class, 'index'])->name('non-ordering-outlets.index');
    Route::get('non-ordering-outlets-datatable', [NonOrderingOutletController::class, 'datatable'])->name('non-ordering-outlets.datatable');
    Route::post('non-ordering-outlets-export-pdf', [NonOrderingOutletController::class, 'exportPdf'])->name('non-ordering-outlets.export-pdf');
    Route::get('non-ordering-outlets-get-sbu-options', [NonOrderingOutletController::class, 'getSbuOptions'])->name('non-ordering-outlets.get-sbu-options');
    Route::get('non-ordering-outlets-get-tp-by-sbu', [NonOrderingOutletController::class, 'getTpBySbu'])->name('non-ordering-outlets.get-tp-by-sbu');
    Route::get('non-ordering-outlets-get-cities-by-tp', [NonOrderingOutletController::class, 'getCitiesByTp'])->name('non-ordering-outlets.get-cities-by-tp');
    Route::get('non-ordering-outlets-get-districts-by-city', [NonOrderingOutletController::class, 'getDistrictsByCity'])->name('non-ordering-outlets.get-districts-by-city');

    // Outlet List Menu
    Route::get('outlet-list', [OutletListController::class, 'index'])->name('outlet-list.index');
    Route::get('outlet-list-datatable', [OutletListController::class, 'datatable'])->name('outlet-list.datatable');
    Route::get('outlet-list-piutang-detail', [OutletListController::class, 'getPiutangDetail'])->name('outlet-list.piutang-detail');
    Route::get('outlet-list-get-sbu-options', [OutletListController::class, 'getSbuOptions'])->name('outlet-list.get-sbu-options');
    Route::get('outlet-list-get-tp', [OutletListController::class, 'getTp'])->name('outlet-list.get-tp');
    Route::get('outlet-list-get-cities-by-tp', [OutletListController::class, 'getCitiesByTp'])->name('outlet-list.get-cities-by-tp');
    Route::get('outlet-list-get-districts', [OutletListController::class, 'getDistrictsByTpCity'])->name('outlet-list.get-districts');
    Route::get('get-cities-by-residency', [OutletListController::class, 'getCitiesByResidency'])->name('get-cities-by-residency');
    Route::get('get-districts-by-city', [OutletListController::class, 'getDistrictsByCity'])->name('get-districts-by-city');
});

// Bypass Outlet - accessible by Admin, Leader, Head Telemarketing, IT Pusat
Route::middleware(['auth', 'role:Admin,Leader,Head Telemarketing,IT Pusat'])->group(function () {
    Route::get('bypass-outlet', [BypassOutletController::class, 'index'])->name('bypass-outlet.index');
    Route::get('bypass-outlet-datatable', [BypassOutletController::class, 'datatable'])->name('bypass-outlet.datatable');
    Route::get('bypass-outlet/{id}', [BypassOutletController::class, 'edit'])->name('bypass-outlet.edit');
    Route::get('bypass-outlet-cities-by-tp', [BypassOutletController::class, 'getCitiesByTp'])->name('bypass-outlet.cities-by-tp');
    Route::get('bypass-outlet-districts-by-city', [BypassOutletController::class, 'getDistrictsByCity'])->name('bypass-outlet.districts-by-city');
    Route::get('bypass-outlet-outlets-by-district', [BypassOutletController::class, 'getOutletsByDistrict'])->name('bypass-outlet.outlets-by-district');

    // Approval Bypass Outlet
    Route::get('approval-bypass-outlet', [ApprovalBypassOutletController::class, 'index'])->name('approval-bypass-outlet.index');
    Route::get('approval-bypass-outlet-datatable', [ApprovalBypassOutletController::class, 'datatable'])->name('approval-bypass-outlet.datatable');
    Route::get('approval-bypass-outlet/{id}', [ApprovalBypassOutletController::class, 'show'])->name('approval-bypass-outlet.show');
});

// Bypass Outlet Input - only Leader, IT Pusat, and Admin can create/edit/delete
Route::middleware(['auth', 'role:Admin,Leader,IT Pusat'])->group(function () {
    Route::get('bypass-outlet/create', [BypassOutletController::class, 'create'])->name('bypass-outlet.create');
    Route::post('bypass-outlet', [BypassOutletController::class, 'store'])->name('bypass-outlet.store');
    Route::put('bypass-outlet/{id}', [BypassOutletController::class, 'update'])->name('bypass-outlet.update');
    Route::delete('bypass-outlet/{id}', [BypassOutletController::class, 'destroy'])->name('bypass-outlet.destroy');
});

// Bypass Outlet Approval - only Head Telemarketing and Admin can approve/reject
Route::middleware(['auth', 'role:Admin,Head Telemarketing'])->group(function () {
    Route::post('approval-bypass-outlet/{id}/approve', [ApprovalBypassOutletController::class, 'approve'])->name('approval-bypass-outlet.approve');
    Route::post('approval-bypass-outlet/{id}/reject', [ApprovalBypassOutletController::class, 'reject'])->name('approval-bypass-outlet.reject');
});
Route::middleware(['auth', 'role:Admin,Telemarketing'])->group(function () {
    Route::resource('inquiry', InquiryController::class);
    Route::resource('complaints', ComplainController::class);

    Route::put('transactions/{transaction}/closeOrder', [TransactionController::class, 'closeOrder'])->name('transactions.close');
    Route::put('transactions/{transaction}/cancel', [TransactionController::class, 'cancel'])->name('transactions.cancel');
    Route::put('inquiry/{inquiry}/cancel', [InquiryController::class, 'cancel'])->name('inquiry.cancel');
    Route::put('complaints/{complaint}/cancel', [ComplainController::class, 'cancel'])->name('complaints.cancel');

    Route::get('credit/{outlet}', [InquiryController::class, 'creditByOutlet'])->name('outlet.credit');
    // Route::get('products/{outlet}', [TransactionController::class, 'productByOutlet'])->name('outlet.products');

    Route::put('inquiry/{inquiry}/status', [InquiryController::class, 'updateStatus'])->name('inquiry.update-status');
    Route::put('complaints/{complaints}/status', [ComplainController::class, 'updateStatus'])->name('complaints.update-status');

    Route::get('dso-order', [DsoOrderController::class, 'index'])->name('dso-order.index');
    Route::post('dso-order/{id}', [DsoOrderController::class, 'store'])->name('dso-order.store');
    Route::get('dso-order/{id}/{outlet}', [DsoOrderController::class, 'detailOrderDso'])->name('dso-order.detail');

    Route::get('inquiry-datatable', [InquiryController::class, 'datatable'])->name('inquiry.datatable');
    Route::get('complaints-datatable', [ComplainController::class, 'datatable'])->name('complaints.datatable');

    Route::patch('transactions/{transaction}/updateDeliveryDate', [TransactionController::class, 'updateDeliveryDate'])->name('transactions.updateDeliveryDate');
    Route::post('transactions/{transaction}/detail', [TransactionController::class, 'storeDetail'])->name('transactions.store.detail');
    Route::delete('transactions/{transaction}/detail/{detail}', [TransactionController::class, 'destroyDetail'])->name('transactions.detail.destroy');
});

Route::middleware(['auth', 'role:Admin,Chakra'])->group(function () {
    Route::put('vehicle/{vehicle}', [TransactionController::class, 'updateVehicle'])->name('vehicle.update');
});

Route::middleware(['auth', 'role:Admin,Telemarketing,Chakra'])->group(function () {
    Route::get('vehicle', [TransactionController::class, 'vehicle'])->name('vehicle.index');
});

Route::middleware(['auth', 'role:Admin,Telemarketing,Chakra,Order Handling,Kepala Non Operasional,Head Telemarketing,Leader,IT Pusat'])->group(function () {
    Route::get('outlet-call-logs', [OutletCallLogController::class, 'index'])->name('outlet-call-logs.index');
    Route::get('outlet-call-logs/datatable', [OutletCallLogController::class, 'datatable'])->name('outlet-call-logs.datatable');
    Route::get('outlet-call-logs/stats', [OutletCallLogController::class, 'getStats'])->name('outlet-call-logs.stats');
    Route::get('outlet-call-logs/{log}', [OutletCallLogController::class, 'show'])->name('outlet-call-logs.show');
});

Route::middleware(['auth', 'role:Order Handling,Telemarketing'])->group(function () {
    Route::put('transactions/{transaction}/validate', [TransactionController::class, 'validateTransaction'])->name('transactions.validate');
});

Route::prefix('datatable')->name('dt.')->group(function () {
    Route::get('tp-call', [DashboardController::class, 'datatableTpCall'])->name('tp-call');
    Route::get('city-call', [DashboardController::class, 'datatableCityCall'])->name('city-call');
    Route::get('outlet-call', [OutletCallController::class, 'datatable'])->name('outlet-call');
    Route::get('approve-outlet-call', [ApproveOutletCallController::class, 'datatable'])->name('approve-outlet-call');
    Route::get('outlet-list', [DashboardController::class, 'datatableOutletList'])->name('outlet-list');
});

// Non-ordering outlets routes
Route::middleware(['auth', 'role:Admin,Telemarketing,Chakra,Order Handling,Kepala Non Operasional,Head Telemarketing,Leader,IT Pusat,Cs'])->group(function () {
    Route::get('get-non-ordering-categories', [DashboardController::class, 'getNonOrderingCategories'])->name('get-non-ordering-categories');
    Route::post('store-non-ordering-outlet', [DashboardController::class, 'storeNonOrderingOutlet'])->name('store-non-ordering-outlet');
});
