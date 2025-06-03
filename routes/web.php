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
use App\Http\Controllers\ReportLettersOutController;

Route::middleware(["localization"])->group(function(){
    // Jadikan LandingPage sebagai halaman utama
    Route::get('/',[LandingPageController::class,'index'])->name('landing');
    
    // Pindahkan login ke path /login atau /auth
    Route::get('/login',[LoginController::class,'index'])->name('login');
    Route::post('/login',[LoginController::class,'auth'])->name('login.auth');
});

Route::middleware(['auth', "localization"])-> group(function(){
    Route::get('/dashboard',[DashboardController::class,'index'])->name('dashboard');

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

    // Add these routes to your routes/web.php file

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
        
        // File Management Routes - PERBAIKAN: Hapus duplikasi /surat/
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


 Route::prefix('surat.keluar')->name('surat.keluar.')->group(function () {
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
});

Route::prefix('report-letter')->group(function () {
    // Laporan Surat Masuk
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
   // Route dengan prefix admin/cuti untuk menyesuaikan URL structure
Route::middleware(['auth'])->prefix('admin/cuti')->name('leave-application.')->group(function () {
    // Display the leave application page
    Route::get('/leave-application', [LeaveApplicationController::class, 'index'])->name('index');
    
    // Get list of leave applications for DataTables (AJAX)
    Route::get('/leave-application/list', [LeaveApplicationController::class, 'list'])->name('list');
    
    // Save new leave application
    Route::post('/leave-application/save', [LeaveApplicationController::class, 'save'])->name('save');
    
    // Get leave application details
    Route::post('/leave-application/detail', [LeaveApplicationController::class, 'detail'])->name('detail');
    
    // Update leave application
    Route::post('/leave-application/update', [LeaveApplicationController::class, 'update'])->name('update');
    
    // Delete leave application
    Route::delete('/leave-application/delete', [LeaveApplicationController::class, 'delete'])->name('delete');
    
    // Approve leave application (for managers/admin)
    Route::post('/leave-application/approve', [LeaveApplicationController::class, 'approve'])->name('approve');
    
    // Reject leave application (for managers/admin)
    Route::post('/leave-application/reject', [LeaveApplicationController::class, 'reject'])->name('reject');
});


// Rute validasi cuti (hanya bisa diakses oleh administrator)
Route::middleware(['auth'])->group(function () {
    // Leave Validation Routes (Admin Only)
    Route::get('/admin/leave-validation', [LeaveValidationController::class, 'index'])->name('leave-validation.index');
    Route::get('/admin/leave-validation/list', [LeaveValidationController::class, 'list'])->name('leave-validation.list');
    Route::post('/admin/leave-validation/detail', [LeaveValidationController::class, 'detail'])->name('leave-validation.detail');
    Route::post('/admin/leave-validation/approve', [LeaveValidationController::class, 'approve'])->name('leave-validation.approve');
    Route::post('/admin/leave-validation/reject', [LeaveValidationController::class, 'reject'])->name('leave-validation.reject');
    Route::post('/admin/leave-validation/process', [LeaveValidationController::class, 'process'])->name('leave-validation.process');
});

 //pengembalian return
Route::middleware(['auth'])->group(function () {
    Route::get('/return', [App\Http\Controllers\ReturnController::class, 'index'])->name('return.index');
    Route::post('/return/save', [App\Http\Controllers\ReturnController::class, 'save'])->name('return.save');
    Route::get('/return/{id}/edit', [App\Http\Controllers\ReturnController::class, 'edit'])->name('return.edit');
    Route::put('/return/{id}', [App\Http\Controllers\ReturnController::class, 'update'])->name('return.update');
    Route::delete('/return/{id}', [App\Http\Controllers\ReturnController::class, 'delete'])->name('return.delete');
});


    // Transaksi  masuk
    Route::controller(TransactionInController::class)->prefix('/transaksi/masuk')->group(function(){
        Route::get('/','index')->name('transaksi.masuk');
        Route::get('/list','list')->name('transaksi.masuk.list');
        Route::post('/save','save')->name('transaksi.masuk.save');
        Route::post('/detail','detail')->name('transaksi.masuk.detail');
        Route::put('/update','update')->name('transaksi.masuk.update');
        Route::delete('/delete','delete')->name('transaksi.masuk.delete');
        Route::get('/barang/list/in','listIn')->name('barang.list.in');
    });



    // Transaksi keluar
    Route::controller(TransactionOutController::class)->prefix('/transaksi/keluar')->group(function(){
        Route::get('/','index')->name('transaksi.keluar');
        Route::get('/list','list')->name('transaksi.keluar.list');
        Route::post('/simpan','save')->name('transaksi.keluar.save');
        Route::post('/info','detail')->name('transaksi.keluar.detail');
        Route::put('/ubah','update')->name('transaksi.keluar.update');
        Route::delete('/hapus','delete')->name('transaksi.keluar.delete');
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

    // laporan stok barang
    Route::controller(ReportStockController::class)->prefix('/laporan/stok')->group(function(){
        Route::get('/','index')->name('laporan.stok');
        Route::get('/list','list')->name('laporan.stok.list');
        Route::get('/grafik','grafik')->name('laporan.stok.grafik');
    });

    // laporan penghasilan
    Route::get('/report/income',[ReportFinancialController::class,'income'])->name('laporan.pendapatan');

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

    // Route::get('/pengaturan/web',[WebSettingController::class,'index'])->name('settings.web');
    // Route::get('/pengaturan/web/detail',[WebSettingController::class,'detail'])->name('settings.web.detail');
    // Route::post('/pengaturan/web/detail/role',[WebSettingController::class,'detailRole'])->name('settings.web.detail.role');
    // Route::put('/pengaturan/web/update',[WebSettingController::class,'update'])->name('settings.web.update');

    // pengaturan profile
    Route::get('/settings/profile',[ProfileController::class,'index'])->name('settings.profile');
    Route::post('/settings/profile',[ProfileController::class,'update'])->name('settings.profile.update');

    // logout
    Route::get('/logout',[LoginController::class,'logout'])->name('login.delete');
