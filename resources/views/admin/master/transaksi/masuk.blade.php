@extends('layouts.app')
@section('title',__("incoming transaction"))
@section('content')
<x-head-datatable/>

<!-- Tambahan CSS untuk Modal Validasi Sederhana -->
<style>
/* Simple Validation Modal */
.simple-validation-modal .modal-dialog {
    max-width: 400px;
}

.simple-validation-modal .modal-content {
    border-radius: 10px;
    border: none;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
}

.simple-validation-modal .modal-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    border-radius: 10px 10px 0 0;
    padding: 15px 20px;
}

.simple-validation-modal .modal-title {
    font-size: 18px;
    font-weight: 600;
    color: #495057;
    margin: 0;
}

.simple-validation-modal .close {
    color: #6c757d;
    opacity: 0.8;
    font-size: 20px;
}

.simple-validation-modal .close:hover {
    opacity: 1;
    color: #495057;
}

.simple-validation-modal .modal-body {
    padding: 30px 20px;
    text-align: center;
}

.warning-icon {
    width: 60px;
    height: 60px;
    background-color: #ffc107;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
}

.warning-icon i {
    font-size: 24px;
    color: white;
}

.warning-message {
    font-size: 16px;
    color: #495057;
    margin-bottom: 20px;
    line-height: 1.5;
}

.error-details {
    background-color: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 20px;
    text-align: left;
}

.error-item {
    color: #dc3545;
    font-size: 14px;
    margin-bottom: 8px;
    display: flex;
    align-items: flex-start;
}

.error-item:last-child {
    margin-bottom: 0;
}

.error-item i {
    margin-right: 8px;
    margin-top: 2px;
    font-size: 12px;
}

.simple-validation-modal .modal-footer {
    border-top: 1px solid #dee2e6;
    padding: 15px 20px;
    justify-content: center;
}

.btn-ok {
    background-color: #007bff;
    border-color: #007bff;
    color: white;
    padding: 8px 30px;
    border-radius: 5px;
    font-size: 14px;
    font-weight: 500;
}

.btn-ok:hover {
    background-color: #0056b3;
    border-color: #0056b3;
    color: white;
}

.btn-ok:focus {
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    color: white;
}
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card w-100">
                <div class="card-header row">
                    <div class="d-flex justify-content-end align-items-center w-100">
                        {{-- Tombol tambah data hanya untuk employee --}}
                        @if(Auth::user()->role->name == 'employee')
                        <button class="btn btn-success" type="button" data-toggle="modal" data-target="#TambahData" id="modal-button">
                            <i class="fas fa-plus"></i> {{__('add data')}}
                        </button>
                        @endif
                    </div>
                </div>

                <!-- Modal Barang -->
                <div class="modal fade" id="modal-barang" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                    <div class="modal-dialog  modal-xl modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="staticBackdropLabel">{{__('select items')}}</h5>
                                <button type="button" class="close" id="close-modal-barang" >
                                <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="data-barang" width="100%"  class="table table-bordered text-nowrap border-bottom dataTable no-footer dtr-inline collapsed">
                                        <thead>
                                            <tr>
                                                <th class="border-bottom-0" width="8%">{{__('no')}}</th>
                                                <th class="border-bottom-0">{{__('photo')}}</th>
                                                <th class="border-bottom-0">{{__('item code')}}</th>
                                                <th class="border-bottom-0">{{__('name')}}</th>
                                                <th class="border-bottom-0">{{__('type')}}</th>
                                                <th class="border-bottom-0">{{__('unit')}}</th>
                                                <th class="border-bottom-0">{{__('brand')}}</th>
                                                <th class="border-bottom-0">{{__('current stock')}}</th>
                                                <th class="border-bottom-0" width="1%">{{__('action')}}</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal -->
                <div class="modal fade" id="TambahData" tabindex="-1" aria-labelledby="TambahDataModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="TambahDataModalLabel">{{__('create incoming transactions')}}</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true"  >&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-7">
                                    <div class="form-group">
                                        <label for="kode" class="form-label">{{__('incoming item code')}} <span class="text-danger">*</span></label>
                                        <input type="text" name="kode" readonly class="form-control">
                                        <input type="hidden" name="id"/>
                                        <input type="hidden" name="id_barang"/>
                                    </div>
                                    <div class="form-group">
                                        <label for="tanggal_masuk" class="form-label">{{__('date of entry')}} <span class="text-danger">*</span></label>
                                        <input type="date" name="tanggal_masuk" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label for="supplier" class="form-label">{{__('choose a supplier')}}<span class="text-danger">*</span></label>
                                        <select name="supplier" class="form-control">
                                            <option selected value="-- Pilih Supplier --">-- {{__('choose a supplier')}} --</option>
                                            @foreach( $suppliers as $supplier)
                                            <option value="{{$supplier->id}}">{{$supplier->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <label for="kode_barang" class="form-label">{{__('item code')}} <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="text" name="kode_barang" class="form-control">
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-primary" type="button" id="cari-barang"><i class="fas fa-search"></i></button>
                                            <button class="btn btn-success" type="button" id="barang"><i class="fas fa-box"></i></button>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="nama_barang" class="form-label">{{__("item name")}}</label>
                                        <input type="text" name="nama_barang" id="nama_barang" readonly class="form-control">
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label for="satuan_barang" class="form-label">{{__("unit")}}</label>
                                                <input type="text" name="satuan_barang" readonly class="form-control">
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-group">
                                                <label for="jenis_barang" class="form-label">{{__("type")}}</label>
                                                <input type="text" name="jenis_barang" readonly class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="jumlah" class="form-label">{{__("incoming amount")}}<span class="text-danger">*</span></label>
                                        <input type="number" name="jumlah"  class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal" id="kembali">{{__("cancel")}}</button>
                            <button type="button" class="btn btn-success" id="simpan">{{__("save")}}</button>
                        </div>
                        </div>
                    </div>
                </div>

                <!-- Simple Validation Modal -->
                <div class="modal fade simple-validation-modal" id="simpleValidationModal" tabindex="-1" aria-labelledby="simpleValidationModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="simpleValidationModalLabel">{{__('Peringatan')}}</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="warning-icon">
                                    <i class="fas fa-exclamation"></i>
                                </div>
                                <div class="warning-message" id="warningMessage">
                                    {{__('Data tidak boleh kosong!')}}
                                </div>
                                <div class="error-details" id="errorDetails" style="display: none;">
                                    <!-- Error items will be inserted here -->
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-ok" data-dismiss="modal">OK</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table id="data-tabel" width="100%"  class="table table-bordered text-nowrap border-bottom dataTable no-footer dtr-inline collapsed">
                            <thead>
                                <tr>
                                    <th class="border-bottom-0" width="8%">{{__("no")}}</th>
                                    <th class="border-bottom-0">{{__("date of entry")}}</th>
                                    <th class="border-bottom-0">{{__("incoming item code")}}</th>
                                    <th class="border-bottom-0">{{__("item code")}}</th>
                                    <th class="border-bottom-0">{{__("supplier")}}</th>
                                    <th class="border-bottom-0">{{__("item")}}</th>
                                    <th class="border-bottom-0">{{__("incoming amount")}}</th>
                                    {{-- Kolom action hanya untuk employee --}}
                                    @if(Auth::user()->role->name == 'employee')
                                    <th class="border-bottom-0" width="20%">{{__("action")}}</th>
                                    @endif
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<x-data-table/>

<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Kirim role user ke JS
    const userRole = "{{ Auth::user()->role->name }}";

    function pilih(){

    }

    // Fungsi untuk menampilkan modal validasi sederhana
    function showSimpleValidation(errors = null, customMessage = null) {
        const warningMessageEl = document.getElementById('warningMessage');
        const errorDetailsEl = document.getElementById('errorDetails');
        
        if (customMessage) {
            warningMessageEl.textContent = customMessage;
        } else {
            warningMessageEl.textContent = 'Data tidak boleh kosong!';
        }
        
        if (errors && errors.length > 0) {
            errorDetailsEl.style.display = 'block';
            errorDetailsEl.innerHTML = '';
            
            errors.forEach(error => {
                const errorItem = document.createElement('div');
                errorItem.className = 'error-item';
                errorItem.innerHTML = `
                    <i class="fas fa-circle"></i>
                    <span>${error}</span>
                `;
                errorDetailsEl.appendChild(errorItem);
            });
        } else {
            errorDetailsEl.style.display = 'none';
        }
        
        $('#simpleValidationModal').modal('show');
    }

    function load(){
        $('#data-barang').DataTable({
            lengthChange: true,
            processing:true,
            serverSide:true,
            ajax:{
                url: `{{route('barang.list')}}`,
                data: {
                    for_modal: true
                }
            },
            columns:[
                {
                    "data":null,"sortable":false,
                    render:function(data,type,row,meta){
                        return meta.row + meta.settings._iDisplayStart+1;
                    }
                },
                {
                    data:'img',
                    name:'img'
                },{
                    data:'code',
                    name:'code'
                },{
                    data:'name',
                    name:'name'
                },{
                    data:'category_name',
                    name:'category_name'
                },
                {
                    data:'unit_name',
                    name:'unit_name'
                },
                {
                    data:'brand_name',
                    name:'brand_name'
                },
                {
                    data:'quantity_formatted',
                    name:'quantity_formatted'
                },
                {
                    data:'tindakan',
                    name:'tindakan'
                }
            ]
        }).buttons().container();
    }

    function isi(){
        let columns = [
            {
                "data": null,
                "sortable": false,
                render: function(data, type, row, meta){
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },
            {
                data:"date_received_formatted",
                name:"date_received"
            },
            {
                data:"invoice_number",
                name:"invoice_number"
            },{
                data:"kode_barang",
                name:"kode_barang"
            },
            {
                data:"supplier_name",
                name:"supplier_name"
            },{
                data:"item_name",
                name:"item_name"
            },
            {
                data:"quantity_formatted",
                name:"quantity"
            }
        ];

        // Kolom action hanya untuk employee
        if(userRole === 'employee'){
            columns.push({
                data: 'tindakan',
                name: 'tindakan',
                orderable: false,
                searchable: false
            });
        }

        $('#data-tabel').DataTable({
            responsive: true,
            lengthChange: true,
            autoWidth: false,
            processing: true,
            serverSide: true,
            ajax: `{{route('transaksi.masuk.list')}}`,
            columns: columns
        }).buttons().container();
    }

    function detail(){
        const kode_barang = $("input[name='kode_barang']").val();
        $.ajax({
            url:`{{route('barang.code')}}`,
            type:'post',
            data:{
                code:kode_barang,
                "_token":"{{csrf_token()}}"
            },
            success:function({data}){
                $("input[name='id_barang']").val(data.id);
                $("input[name='nama_barang']").val(data.name);
                $("input[name='satuan_barang']").val(data.unit_name);
                $("input[name='jenis_barang']").val(data.category_name);
            },
            error:function(xhr){
                console.log(xhr.responseJSON);
                showSimpleValidation(null, 'Item tidak ditemukan');
            }
        });
    }

    function simpan(){
        // Validasi client-side terlebih dahulu
        const errors = [];
        
        // Validate supplier
        const supplier = $("select[name='supplier']").val();
        if (!supplier || supplier === "-- Pilih Supplier --") {
            errors.push('{{__("Supplier harus dipilih")}}');
        }

        // Validate item
        const itemId = $("input[name='id_barang']").val();
        if (!itemId) {
            errors.push('{{__("Item wajib dipilih")}}');
        }

        // Validate date
        const dateReceived = $("input[name='tanggal_masuk']").val();
        if (!dateReceived) {
            errors.push('{{__("Tanggal masuk wajib diisi")}}');
        }

        // Validate quantity
        const quantity = $("input[name='jumlah']").val();
        if (!quantity || quantity <= 0) {
            errors.push('{{__("Jumlah masuk harus lebih dari 0")}}');
        }

        // Validate item code
        const itemCode = $("input[name='kode_barang']").val();
        if (!itemCode) {
            errors.push('{{__("Kode barang wajib diisi")}}');
        }

        // Jika ada error validasi, tampilkan modal
        if (errors.length > 0) {
            showSimpleValidation(errors);
            return;
        }

        $('#simpan').prop('disabled', true);

        // Jika validasi lolos, lanjutkan dengan AJAX
        const item_id =  $("input[name='id_barang']").val();
        const user_id = `{{Auth::user()->id}}`;
        const date_received = $("input[name='tanggal_masuk'").val();
        const supplier_id = $("select[name='supplier'").val();
        const invoice_number = $("input[name='kode'").val();

        const Form = new FormData();
        Form.append('user_id',user_id);
        Form.append('item_id',item_id);
        Form.append('date_received', date_received );
        Form.append('quantity', quantity );
        Form.append('supplier_id', supplier_id );
        Form.append('invoice_number', invoice_number );
        Form.append('_token', '{{csrf_token()}}');
        
        $.ajax({
            url:`{{route('transaksi.masuk.save')}}`,
            type:"post",
            processData: false,
            contentType: false,
            dataType: 'json',
            data:Form,
            success:function(res){
                    Swal.fire({
                        position: "center",
                        icon: "success",
                        title: res.message,
                        showConfirmButton: false,
                        timer: 1500
                    });
                    $('#kembali').click();
                    resetForm();
                    $('#data-tabel').DataTable().ajax.reload();
                },
                error:function(err){
                    let message = 'An error occurred';
                    if(err.responseJSON && err.responseJSON.message){
                        message = err.responseJSON.message;
                    } else if(err.responseJSON && err.responseJSON.errors){
                        const serverErrors = [];
                        Object.keys(err.responseJSON.errors).forEach(function(key) {
                            serverErrors.push(err.responseJSON.errors[key][0]);
                        });
                        showSimpleValidation(serverErrors);
                        return;
                    }

                    Swal.fire({
                        position: "center",
                        icon: "error",
                        title: message,
                        showConfirmButton: false,
                        timer: 2000
                    });
            },
            complete: function(){
                $('#simpan').prop('disabled', false);
            }
        })
    }

    function ubah(){
        // Validasi client-side terlebih dahulu untuk update
        const errors = [];
        
        // Validate supplier
        const supplier = $("select[name='supplier']").val();
        if (!supplier || supplier === "-- Pilih Supplier --") {
            errors.push('{{__("Supplier harus dipilih")}}');
        }

        // Validate item
        const itemId = $("input[name='id_barang']").val();
        if (!itemId) {
            errors.push('{{__("Item wajib dipilih")}}');
        }

        // Validate date
        const dateReceived = $("input[name='tanggal_masuk']").val();
        if (!dateReceived) {
            errors.push('{{__("Tanggal masuk wajib diisi")}}');
        }

        // Validate quantity
        const quantity = $("input[name='jumlah']").val();
        if (!quantity || quantity <= 0) {
            errors.push('{{__("Jumlah masuk harus lebih dari 0")}}');
        }

        // Jika ada error validasi, tampilkan modal
        if (errors.length > 0) {
            showSimpleValidation(errors);
            return;
        }

        $('#simpan').prop('disabled', true);

        // Jika validasi lolos, lanjutkan dengan AJAX update
        const id =  $("input[name='id']").val();
        const item_id =  $("input[name='id_barang']").val();
        const user_id = `{{Auth::user()->id}}`;
        const date_received = $("input[name='tanggal_masuk'").val();
        const supplier_id = $("select[name='supplier'").val();
        const invoice_number = $("input[name='kode'").val();
        
        $.ajax({
            url:`{{route('transaksi.masuk.update')}}`,
            type:"put",
            data:{
                id:id,
                item_id:item_id,
                user_id:user_id,
                date_received:date_received,
                supplier_id:supplier_id,
                invoice_number:invoice_number,
                quantity:quantity,
                "_token":"{{csrf_token()}}"
            },
            success:function(res){
                    Swal.fire({
                        position: "center",
                        icon: "success",
                        title: res.message,
                        showConfirmButton: false,
                        timer: 1500
                    });
                    $('#kembali').click();
                    resetForm();
                    $('#data-tabel').DataTable().ajax.reload();
                },
                error:function(err){
                    let message = 'An error occurred';
                    if(err.responseJSON && err.responseJSON.message){
                        message = err.responseJSON.message;
                    } else if(err.responseJSON && err.responseJSON.errors){
                        const serverErrors = [];
                        Object.keys(err.responseJSON.errors).forEach(function(key) {
                            serverErrors.push(err.responseJSON.errors[key][0]);
                        });
                        showSimpleValidation(serverErrors);
                        return;
                    }

                    Swal.fire({
                        position: "center",
                        icon: "error",
                        title: message,
                        showConfirmButton: false,
                        timer: 2000
                    });
            },
            complete: function(){
                $('#simpan').prop('disabled', false);
            }
        })
    }

    function resetForm(){
        $("input[name='id']").val('');
        $("input[name='id_barang']").val('');
        $("select[name='supplier'").val("-- {{__('choose a supplier')}} --");
        $("input[name='nama_barang']").val('');
        $("input[name='kode_barang']").val('');
        $("input[name='jenis_barang']").val('');
        $("input[name='satuan_barang']").val('');
        $("input[name='jumlah']").val('');
        $("input[name='tanggal_masuk']").val('');
        $("#simpan").text("{{ __('save') }}");
    }

    $(document).ready(function(){
        load();
        isi();

        // pilih data barang
        $(document).on("click",".pilih-data-barang",function(){
            id = $(this).data("id");
            $.ajax({
                url:"{{route('barang.detail')}}",
                type:"post",
                data:{
                    id:id,
                    "_token":"{{csrf_token()}}"
                },
                success:function({data}){
                    $("input[name='kode_barang']").val(data.code);
                    $("input[name='id_barang']").val(data.id);
                    $("input[name='nama_barang']").val(data.name);
                    $("input[name='satuan_barang']").val(data.unit_name);
                    $("input[name='jenis_barang']").val(data.category_name);
                    $('#modal-barang').modal('hide');
                    $('#TambahData').modal('show');
                }
             });
        });

        $("#barang").on("click",function(){
            $('#modal-barang').modal('show');
            $('#TambahData').modal('hide');
        });
        
        $("#close-modal-barang").on("click",function(){
            $('#modal-barang').modal('hide');
            $('#TambahData').modal('show');
        });
        
        $("#cari-barang").on("click",detail);

        $('#simpan').on('click', function(){
            if($(this).text().includes('Changes') || $(this).text().includes('Update')){
                ubah();
            } else {
                simpan();
            }
        });

        $("#modal-button").on("click", function(){
            id = new Date().getTime();
            $("input[name='kode']").val("BRGMSK-"+id);
            resetForm();
        });

        $('#TambahData').on('hidden.bs.modal', function () {
            resetForm();
        });
    });

    $(document).on("click",".ubah",function(){
        let id = $(this).attr('id');
        $("#modal-button").click();
        $("#simpan").text("{{ __('save') }} Changes");

        $.ajax({
            url:"{{route('transaksi.masuk.detail')}}",
            type:"post",
            data:{
                id:id,
                "_token":"{{csrf_token()}}"
            },
            success:function(response){
                if(response.data){
                    $("input[name='id']").val(response.data.id);
                    $("input[name='kode']").val(response.data.invoice_number);
                    $("input[name='id_barang']").val(response.data.item_id);
                    $("select[name='supplier'").val(response.data.supplier_id);
                    $("input[name='nama_barang']").val(response.data.nama_barang);
                    $("input[name='tanggal_masuk']").val(response.data.date_received);
                    $("input[name='kode_barang']").val(response.data.kode_barang);
                    $("input[name='jenis_barang']").val(response.data.jenis_barang);
                    $("input[name='satuan_barang']").val(response.data.satuan_barang);
                    $("input[name='jumlah']").val(response.data.quantity);
                }
            },
            error: function(err){
                Swal.fire({
                    position: "center",
                    icon: "error",
                    title: "Failed to load data",
                    showConfirmButton: false,
                    timer: 1500
                });
            }
        });
    });

    $(document).on("click",".hapus",function(){
        let id = $(this).attr('id');
        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: "btn btn-success m-1",
                cancelButton: "btn btn-danger m-1"
            },
            buttonsStyling: false
        });
        swalWithBootstrapButtons.fire({
            title: "{{__('you are sure')}} ?",
            text: "{{__('this data will be deleted')}}",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "{{__('yes, delete')}}",
            cancelButtonText: "{{__('no, cancel')}}!",
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url:"{{route('transaksi.masuk.delete')}}",
                    type:"delete",
                    data:{
                        id:id,
                        "_token":"{{csrf_token()}}"
                    },
                    success:function(res){
                        Swal.fire({
                                position: "center",
                                icon: "success",
                                title: res.message,
                                showConfirmButton: false,
                                timer: 1500
                        });
                        $('#data-tabel').DataTable().ajax.reload();
                    },
                    error: function(err){
                        let message = 'Failed to delete data';
                        if(err.responseJSON && err.responseJSON.message){
                            message = err.responseJSON.message;
                        }

                        Swal.fire({
                            position: "center",
                            icon: "error",
                            title: message,
                            showConfirmButton: false,
                            timer: 2000
                        });
                    }
                });
            }
        });
    });

</script>
@endsection