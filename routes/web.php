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
    // Routes untuk pengajuan cuti
Route::prefix('admin/cuti')->middleware(['auth'])->group(function () {
    // Pengajuan cuti
    Route::get('/leave-application', [LeaveApplicationController::class, 'index'])->name('leave-application'); // Route untuk pengajuan cuti
    Route::get('/leave-application/list', [LeaveApplicationController::class, 'list'])->name('leave-application.list');
    Route::post('/leave-application/save', [LeaveApplicationController::class, 'save'])->name('leave-application.save');
    Route::post('/leave-application/detail', [LeaveApplicationController::class, 'detail'])->name('leave-application.detail');
    Route::post('/leave-application/update', [LeaveApplicationController::class, 'update'])->name('leave-application.update');
    Route::post('/leave-application/delete', [LeaveApplicationController::class, 'delete'])->name('leave-application.delete');
});

// Validasi cuti (untuk kepala unit)
// Rute validasi cuti (hanya bisa diakses oleh administrator)
Route::middleware(['admin.check'])->group(function () {
    Route::get('/admin/leave-validation', [App\Http\Controllers\LeaveValidationController::class, 'index'])->name('leave-validation');
    Route::post('/admin/leave-validation/list', [App\Http\Controllers\LeaveValidationController::class, 'list'])->name('leave-validation.list');
    Route::post('/admin/leave-validation/detail', [App\Http\Controllers\LeaveValidationController::class, 'detail'])->name('leave-validation.detail');
    Route::post('/admin/leave-validation/approve', [App\Http\Controllers\LeaveValidationController::class, 'approve'])->name('leave-validation.approve');
    Route::post('/admin/leave-validation/reject', [App\Http\Controllers\LeaveValidationController::class, 'reject'])->name('leave-validation.reject');
});

 
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

});