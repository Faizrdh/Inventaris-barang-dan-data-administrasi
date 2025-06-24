<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CutiController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\TransactionInController;
use App\Http\Controllers\TransactionOutController;
use App\Http\Controllers\ReportGoodsInController;
use App\Http\Controllers\ReportGoodsOutController;
use App\Http\Controllers\ReportStockController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\WebSettingController;
use App\Http\Controllers\AdminatorController;
use App\Http\Controllers\LeaveApplicationController;
use App\Http\Controllers\LeaveApprovalController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportFinancialController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\CategoryLetterController;
use App\Http\Controllers\LeaveValidationController;
use App\Http\Controllers\LetterController;
use App\Http\Controllers\ReportLetterInController;
use App\Http\Controllers\ReportLettersInController;
use App\Http\Controllers\SenderLetterController;
use App\Http\Controllers\LettersInController;
use App\Http\Controllers\LettersOutController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportLettersOutController;
use App\Http\Controllers\ReturnController;

Route::middleware(["localization"])->group(function(){
    // Jadikan LandingPage sebagai halaman utama
    Route::get('/',[LandingPageController::class,'index'])->name('landing');
    
    // Pindahkan login ke path /login atau /auth
    Route::get('/login',[LoginController::class,'index'])->name('login');
    Route::post('/login',[LoginController::class,'auth'])->name('login.auth');
});

Route::middleware(['auth', "localization"])->group(function(){
    Route::get('/dashboard',[DashboardController::class,'index'])->name('dashboard');

   Route::middleware(['auth'])->group(function () {
    // Notification endpoints - SUPER SIMPLE
        Route::get('/notifications', [App\Http\Controllers\NotificationController::class, 'getNotifications']);
    Route::post('/notifications/mark-read', [App\Http\Controllers\NotificationController::class, 'markAsRead']);
    Route::post('/notifications/cleanup-deleted', [App\Http\Controllers\NotificationController::class, 'cleanupDeletedItems']);
    Route::get('/notifications/counts', [App\Http\Controllers\NotificationController::class, 'getCounts']);
    Route::post('/notifications/clear-read', [App\Http\Controllers\NotificationController::class, 'clearRead']);
    
    // Test endpoint (bisa dihapus nanti)
    Route::get('/notifications/test', [NotificationController::class, 'test']);
});
    // barang
    Route::controller(ItemController::class)->prefix("barang")->group(function(){
        Route::get('/','index')->name('barang');
        Route::post('/kode','detailByCode')->name('barang.code');
        Route::get('/daftar-barang','list')->name('barang.list');

        Route::middleware(['employee.middleware'])->group(function(){
            Route::post('/simpan','save')->name('barang.save');
            Route::post('/info','detail')->name('barang.detail');
            Route::post('/ubah','update')->name('barang.update');
            Route::delete('/hapus','delete')->name('barang.delete');
        });
    });

    // jenis barang
    Route::controller(CategoryController::class)->prefix("barang/jenis")->group(function(){
        Route::get('/','index')->name('barang.jenis');
        Route::get('/daftar','list')->name('barang.jenis.list');
        Route::middleware(['employee.middleware'])->group(function(){
            Route::post('/simpan','save')->name('barang.jenis.save');
            Route::post('/info','detail')->name('barang.jenis.detail');
            Route::put('/ubah','update')->name('barang.jenis.update');
            Route::delete('/hapus','delete')->name('barang.jenis.delete');
        });
    });

    // satuan barang
    Route::controller(UnitController::class)->prefix('/barang/satuan')->group(function(){
        Route::get('/','index')->name('barang.satuan');
        Route::get('/daftar','list')->name('barang.satuan.list');
        Route::middleware(['employee.middleware'])->group(function(){
            Route::post('/simpan','save')->name('barang.satuan.save');
            Route::post('/info','detail')->name('barang.satuan.detail');
            Route::put('/ubah','update')->name('barang.satuan.update');
            Route::delete('/hapus','delete')->name('barang.satuan.delete');
        });
    });

    // merk barang
    Route::controller(BrandController::class)->prefix("/barang/merk")->group(function(){
        Route::get('/','index')->name('barang.merk');
        Route::get('/daftar','list')->name('barang.merk.list');
        Route::middleware(['employee.middleware'])->group(function(){
            Route::post('/simpan','save')->name('barang.merk.save');
            Route::post('/info','detail')->name('barang.merk.detail');
            Route::put('/ubah','update')->name('barang.merk.update');
            Route::delete('/hapus','delete')->name('barang.merk.delete');
        });
    });

    //Jenis Surat
    Route::prefix('letter/category')->name('letter.category.')->group(function() {
        Route::get('/', [CategoryLetterController::class, 'index'])->name('index');
        Route::get('/list', [CategoryLetterController::class, 'list'])->name('list');
        Route::post('/save', [CategoryLetterController::class, 'save'])->name('save');
        Route::post('/detail', [CategoryLetterController::class, 'detail'])->name('detail');
        Route::put('/update', [CategoryLetterController::class, 'update'])->name('update');
        Route::delete('/delete', [CategoryLetterController::class, 'delete'])->name('delete');
    });

    // Routes untuk Letter Management
    Route::prefix('surat')->name('surat.')->group(function () {
        Route::get('/', [LetterController::class, 'index'])->name('index');
        Route::get('/list', [LetterController::class, 'list'])->name('list');
        Route::post('/save', [LetterController::class, 'save'])->name('save');
        Route::post('/detail', [LetterController::class, 'detail'])->name('detail');
        Route::post('/detail-by-code', [LetterController::class, 'detailByCode'])->name('detail-by-code');
        Route::put('/update', [LetterController::class, 'update'])->name('update');
        Route::delete('/delete', [LetterController::class, 'delete'])->name('delete');
        
        // File Management Routes
        Route::get('/download-file', [LetterController::class, 'downloadFile'])->name('download-file');
        Route::get('/view-file', [LetterController::class, 'viewFile'])->name('view-file');
        Route::delete('/delete-file', [LetterController::class, 'deleteFile'])->name('delete-file');
    });

    // SenderLetter Routes
    Route::prefix('sender-letter')->group(function () {
        Route::get('/', [SenderLetterController::class, 'index'])->name('sender_letter.index');
        Route::get('/list', [SenderLetterController::class, 'list'])->name('sender_letter.list');
        Route::post('/save', [SenderLetterController::class, 'save'])->name('sender_letter.save');
        Route::post('/detail', [SenderLetterController::class, 'detail'])->name('sender_letter.detail');
        Route::put('/update', [SenderLetterController::class, 'update'])->name('sender_letter.update');
        Route::delete('/delete', [SenderLetterController::class, 'delete'])->name('sender_letter.delete');
    });

    // Routes Surat Masuk (LettersIn)
    Route::prefix('surat-masuk')->name('surat.masuk.')->group(function () {
        Route::get('/', [LettersInController::class, 'index'])->name('index');
        Route::post('/list', [LettersInController::class, 'list'])->name('list');
        Route::post('/list-letters', [LettersInController::class, 'listLetters'])->name('list.letters');
        Route::post('/letter-code', [LettersInController::class, 'getLetterByCode'])->name('letter.code');
        Route::post('/save', [LettersInController::class, 'store'])->name('save');
        Route::post('/detail', [LettersInController::class, 'show'])->name('detail');
        Route::put('/update', [LettersInController::class, 'update'])->name('update');
        Route::delete('/delete', [LettersInController::class, 'destroy'])->name('delete');
    });

    //surat keluar
    Route::prefix('surat-keluar')->name('surat.keluar.')->group(function () {
        Route::get('/', [LettersOutController::class, 'index'])->name('index');
        Route::post('/list', [LettersOutController::class, 'list'])->name('list');
        Route::post('/list-letters', [LettersOutController::class, 'listLetters'])->name('list.letters');
        Route::post('/letter-code', [LettersOutController::class, 'getLetterByCode'])->name('letter.code');
        Route::post('/store', [LettersOutController::class, 'store'])->name('store');
        Route::put('/update', [LettersOutController::class, 'update'])->name('update');
        Route::post('/show', [LettersOutController::class, 'show'])->name('show');
        Route::delete('/destroy', [LettersOutController::class, 'destroy'])->name('destroy');
    });

    // Laporan Surat Keluar
    Route::get('/laporan/surat-keluar', [ReportLettersOutController::class, 'index'])->name('laporan.surat-keluar');
    Route::post('/laporan/surat-keluar/list', [ReportLettersOutController::class, 'list'])->name('laporan.surat-keluar.list');
    Route::post('/laporan/surat-keluar/export-excel', [ReportLettersOutController::class, 'exportExcel'])->name('laporan.surat-keluar.export-excel');
    Route::post('/laporan/surat-keluar/export-pdf', [ReportLettersOutController::class, 'exportPdf'])->name('laporan.surat-keluar.export-pdf');

    // Laporan Surat Masuk
    Route::prefix('report-letter')->group(function () {
        Route::get('/laporan/surat-masuk', [ReportLettersInController::class, 'index'])->name('laporan.surat-masuk');
        Route::get('/laporan/surat-masuk/list', [ReportLettersInController::class, 'list'])->name('laporan.surat-masuk.list');
        Route::post('/laporan/surat-masuk/export-excel', [ReportLettersInController::class, 'exportExcel'])->name('laporan.surat-masuk.export-excel');
        Route::post('/laporan/surat-masuk/export-pdf', [ReportLettersInController::class, 'exportPdf'])->name('laporan.surat-masuk.export-pdf');
    });

    // customer (izin untuk staff hanya read)
    Route::controller(CustomerController::class)->prefix('/customer')->group(function(){
        Route::get('/','index')->name('customer');
        Route::get('/daftar','list')->name('customer.list');
        Route::middleware(['employee.middleware'])->group(function(){
            Route::post('/simpan','save')->name('customer.save');
            Route::post('/info','detail')->name('customer.detail');
            Route::put('/ubah','update')->name('customer.update');
            Route::delete('/hapus','delete')->name('customer.delete');
        });
    });

    // supplier (izin untuk staff hanya read)
    Route::controller(SupplierController::class)->prefix('/supplier')->group(function(){
        Route::get('/','index')->name('supplier');
        Route::get('/daftar','list')->name('supplier.list');
        Route::middleware(['employee.middleware'])->group(function(){
            Route::post('/simpan','save')->name('supplier.save');
            Route::post('/info','detail')->name('supplier.detail');
            Route::put('/ubah','update')->name('supplier.update');
            Route::delete('/hapus','delete')->name('supplier.delete');
        });
    });

    //route Cuti 
    Route::prefix('leave-application')->name('leave-application.')->group(function () {
        Route::get('/', [LeaveApplicationController::class, 'index'])->name('index');
        Route::get('/list', [LeaveApplicationController::class, 'list'])->name('list');
        Route::post('/save', [LeaveApplicationController::class, 'save'])->name('save');
        Route::post('/update', [LeaveApplicationController::class, 'update'])->name('update');
        Route::post('/delete', [LeaveApplicationController::class, 'delete'])->name('delete');
        Route::post('/detail', [LeaveApplicationController::class, 'detail'])->name('detail');
    });

    // Transaksi  masuk
Route::middleware(['auth'])->group(function () {
    
   
    // Routes untuk Transaksi Masuk
   Route::prefix('transaksi-masuk')->group(function () {
    Route::get('/', [TransactionInController::class, 'index'])->name('transaksi.masuk.index');
    Route::get('/list', [TransactionInController::class, 'list'])->name('transaksi.masuk.list');
    Route::post('/save', [TransactionInController::class, 'save'])->name('transaksi.masuk.save');
    Route::post('/detail', [TransactionInController::class, 'detail'])->name('transaksi.masuk.detail');
    Route::put('/update', [TransactionInController::class, 'update'])->name('transaksi.masuk.update');
    Route::delete('/delete', [TransactionInController::class, 'delete'])->name('transaksi.masuk.delete');
    });
    
});

    // Transaksi keluar
   // Tambahkan route ini ke dalam file routes/web.php

Route::controller(TransactionOutController::class)->prefix('/transaksi/keluar')->group(function(){
    Route::get('/','index')->name('transaksi.keluar');
    Route::get('/list','list')->name('transaksi.keluar.list');
    Route::post('/simpan','save')->name('transaksi.keluar.simpan');
    Route::post('/info','detail')->name('transaksi.keluar.info');
    Route::put('/ubah','update')->name('transaksi.keluar.ubah');
    Route::delete('/hapus','delete')->name('transaksi.keluar.hapus');
    Route::post('/cek-stok','getCurrentStock')->name('transaksi.keluar.cek-stok');
    
    // Route stock
    Route::get('/stock','getCurrentStock')->name('transaksi.keluar.stock');
    Route::post('/check-stock','checkStockAvailability')->name('transaksi.keluar.check-stock');
    Route::get('/available-items','getAvailableItems')->name('transaksi.keluar.available-items');
});

    // laporan barang masuk
    Route::controller(ReportGoodsInController::class)->prefix('/laporan/masuk')->group(function(){
        Route::get('/','index')->name('laporan.masuk');
        Route::get('/list','list')->name('laporan.masuk.list');
    });

    // laporan barang keluar
    Route::controller(ReportGoodsOutController::class)->prefix('/laporan/keluar')->group(function(){
        Route::get('/','index')->name('laporan.keluar');
        Route::get('/list','list')->name('laporan.keluar.list');
    });

    // Stock Report Routes - Complete Version
Route::controller(ReportStockController::class)->prefix('laporan/stok')->group(function(){
    Route::get('/', 'index')->name('laporan.stok');
    Route::get('/list', 'list')->name('laporan.stok.list');
    Route::get('/grafik', 'grafik')->name('laporan.stok.grafik');
    
    // Additional routes for stock management features
    Route::get('/movements', 'getMovements')->name('laporan.stok.movements');
    Route::post('/refresh', 'refreshStock')->name('laporan.stok.refresh');
    Route::post('/recalculate', 'recalculateAllStock')->name('laporan.stok.recalculate');
    Route::get('/summary', 'getSummary')->name('laporan.stok.summary');
    Route::post('/export', 'export')->name('laporan.stok.export');
});

// Alternative stock report routes untuk backward compatibility
Route::prefix('admin/master')->group(function () {
    Route::get('/laporan/stok', [ReportStockController::class, 'index'])->name('admin.master.laporan.stok');
    Route::get('/laporan/stok/list', [ReportStockController::class, 'list'])->name('admin.master.laporan.stok.list');
    Route::get('/laporan/stok/grafik', [ReportStockController::class, 'grafik'])->name('admin.master.laporan.stok.grafik');
    Route::get('/laporan/stok/movements', [ReportStockController::class, 'getMovements'])->name('admin.master.laporan.stok.movements');
    Route::post('/laporan/stok/refresh', [ReportStockController::class, 'refreshStock'])->name('admin.master.laporan.stok.refresh');
    Route::post('/laporan/stok/recalculate', [ReportStockController::class, 'recalculateAllStock'])->name('admin.master.laporan.stok.recalculate');
    Route::get('/laporan/stok/summary', [ReportStockController::class, 'getSummary'])->name('admin.master.laporan.stok.summary');
    Route::post('/laporan/stok/export', [ReportStockController::class, 'export'])->name('admin.master.laporan.stok.export');
});


    // pengaturan pengguna
    Route::middleware(['employee.middleware'])->group(function(){
        Route::controller(EmployeeController::class)->prefix('/settings/employee')->group(function(){
            Route::get('/','index')->name('settings.employee');
            Route::get('/list','list')->name('settings.employee.list');
            Route::post('/save','save')->name('settings.employee.save');
            Route::post('/detail','detail')->name('settings.employee.detail');
            Route::put('/update','update')->name('settings.employee.update');
            Route::delete('/delete','delete')->name('settings.employee.delete');
        });
    });

    // pengaturan profile
    Route::get('/settings/profile',[ProfileController::class,'index'])->name('settings.profile');
    Route::post('/settings/profile',[ProfileController::class,'update'])->name('settings.profile.update');

    // logout
    Route::get('/logout',[LoginController::class,'logout'])->name('login.delete');
});

Route::middleware(['auth'])->group(function () {
    // Leave Validation Routes (Admin Only)
    Route::prefix('admin/leave-validation')->name('leave-validation.')->group(function () {
        Route::get('/', [LeaveValidationController::class, 'index'])->name('index');
        Route::get('/list', [LeaveValidationController::class, 'list'])->name('list');
        Route::post('/detail', [LeaveValidationController::class, 'detail'])->name('detail');
        Route::post('/approve', [LeaveValidationController::class, 'approve'])->name('approve');
        Route::post('/reject', [LeaveValidationController::class, 'reject'])->name('reject');
        Route::post('/process', [LeaveValidationController::class, 'process'])->name('process');
    });
});
Route::get('/returns', [ReturnController::class, 'index'])->name('return.index');
Route::get('/returns/list', [ReturnController::class, 'list'])->name('return.list');
Route::post('/returns/save', [ReturnController::class, 'save'])->name('return.save');
Route::post('/returns/detail', [ReturnController::class, 'detail'])->name('return.detail');
Route::put('/returns/update', [ReturnController::class, 'update'])->name('return.update');
Route::delete('/returns/delete', [ReturnController::class, 'delete'])->name('return.delete');
;

