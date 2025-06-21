@extends('layouts.app')
@section('title', __("goods"))
@section('content')
<x-head-datatable/>
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card w-100">
                <div class="card-header row">
                    <div class="d-flex justify-content-end align-items-center w-100">
                    {{-- Tombol tambah data hanya untuk employee --}}
                    @if(Auth::user()->role->name == 'employee')
                        <button class="btn btn-success" type="button" data-toggle="modal" data-target="#TambahData" id="modal-button">
                            <i class="fas fa-plus"></i> {{ __("add data") }}
                        </button>
                    @endif
                    </div>
                </div>

                <!-- Modal Form -->
                <div class="modal fade" id="TambahData" tabindex="-1" aria-labelledby="TambahDataModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="TambahDataModalLabel">{{ __("add goods") }}</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-7">
                                    <div class="form-group">
                                        <label for="kode" class="form-label">{{ __("code of goods") }} <span class="text-danger">*</span></label>
                                        <input type="text" name="kode" readonly class="form-control">
                                        <input type="hidden" name="id"/>
                                    </div>
                                    <div class="form-group">
                                        <label for="nama" class="form-label">{{ __("name of goods") }} <span class="text-danger">*</span></label>
                                        <input type="text" name="nama" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label for="jenisbarang" class="form-label">{{ __("types of goods") }} <span class="text-danger">*</span></label>
                                        <select name="jenisbarang" class="form-control">
                                            <option value="">-- {{ __("select category") }} --</option>
                                            @foreach ($jenisbarang as $jb)
                                                <option value="{{$jb->id}}">{{$jb->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="satuan" class="form-label">{{ __("unit of goods") }} <span class="text-danger">*</span></label>
                                        <select name="satuan" class="form-control">
                                            <option value="">-- {{ __("select unit") }} --</option>
                                            @foreach ($satuan as $s)
                                            <option value="{{$s->id}}">{{$s->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="merk" class="form-label">{{ __("brand of goods") }} <span class="text-danger">*</span></label>
                                        <select name="merk" class="form-control">
                                            <option value="">-- {{ __("select brand") }} --</option>
                                            @foreach ($merk as $m)
                                            <option value="{{$m->id}}">{{$m->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="jumlah" class="form-label">{{ __("Stok awal") }}</label>
                                        <input type="number" value="0" name="jumlah" class="form-control" min="0">
                                        <small class="form-text text-muted">{{ __("Masukan Stok Awal") }}</small>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label for="title" class="form-label">{{ __("photo") }}</label>
                                        <img src="{{asset('default.png')}}" width="80%" alt="profile-user" id="outputImg" class="text-center">
                                        <input class="form-control mt-5" id="GetFile" name="photo" type="file" accept=".png,.jpeg,.jpg,.svg">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal" id="kembali">{{ __("back") }}</button>
                            <button type="button" class="btn btn-success" id="simpan">{{ __("save") }}</button>
                        </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Konfirmasi Delete -->
                <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
                            <div class="modal-body text-center py-5 px-5">
                                <!-- Warning Icon -->
                                <div class="mb-4">
                                    <div class="d-inline-flex justify-content-center align-items-center" 
                                         style="width: 80px; height: 80px; border: 3px solid #ffa726; border-radius: 50%; background-color: #fff3e0;">
                                        <i class="fas fa-exclamation" style="font-size: 2.5rem; color: #ffa726;"></i>
                                    </div>
                                </div>
                                
                                <!-- Title -->
                                <h4 class="mb-3 text-dark font-weight-bold">Apakah Anda Yakin?</h4>
                                
                                <!-- Message -->
                                <p class="text-muted mb-4" style="font-size: 16px;">
                                    Apakah Anda Yakin Menghapus Data Ini?
                                </p>
                                
                                <!-- Item Name -->
                                <div class="mb-4">
                                    <strong id="deleteItemName" class="text-dark" style="font-size: 18px;"></strong>
                                </div>
                                
                                <!-- Buttons -->
                                <div class="d-flex justify-content-center gap-3">
                                    <button type="button" class="btn btn-outline-secondary px-5 py-2" 
                                            data-dismiss="modal" style="border-radius: 25px; min-width: 130px;">
                                        Tidak, Batal!
                                    </button>
                                    <button type="button" class="btn btn-success px-5 py-2" 
                                            id="confirmDelete" style="border-radius: 25px; min-width: 130px;">
                                        Ya, Hapus!
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Error Cannot Delete -->
                <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
                            <div class="modal-body text-center py-5 px-5">
                                <!-- Error Icon -->
                                <div class="mb-4">
                                    <div class="d-inline-flex justify-content-center align-items-center" 
                                         style="width: 80px; height: 80px; border: 3px solid #ef5350; border-radius: 50%; background-color: #ffebee;">
                                        <i class="fas fa-times" style="font-size: 2.5rem; color: #ef5350;"></i>
                                    </div>
                                </div>
                                
                                <!-- Message -->
                                <p class="text-dark mb-4" style="font-size: 18px; font-weight: 500; line-height: 1.5;">
                                    Data ini tidak dapat dihapus dikarenakan digunakan pada data yang lain
                                </p>
                                
                                <!-- Button -->
                                <button type="button" class="btn btn-primary px-5 py-2" 
                                        data-dismiss="modal" style="border-radius: 25px; min-width: 130px;">
                                    OK
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table id="data-tabel" width="100%" class="table table-bordered text-nowrap border-bottom dataTable no-footer dtr-inline collapsed">
                            <thead>
                                <tr>
                                    <th class="border-bottom-0" width="8%">{{ __("no") }}</th>
                                    <th class="border-bottom-0">{{ __("photo") }}</th>
                                    <th class="border-bottom-0">{{ __("code") }}</th>
                                    <th class="border-bottom-0">{{ __("name") }}</th>
                                    <th class="border-bottom-0">{{ __("type") }}</th>
                                    <th class="border-bottom-0">{{ __("unit") }}</th>
                                    <th class="border-bottom-0">{{ __("brand") }}</th>
                                    <th class="border-bottom-0">{{ __("stock") }}</th>
                                    {{-- Kolom action hanya untuk employee --}}
                                    @if(Auth::user()->role->name == 'employee')
                                    <th class="border-bottom-0" width="1%">{{ __("action") }}</th>
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
    // CSRF Token Setup
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Global Variables
    let deleteItemId = null;
    let isEditMode = false;

    // Initialize DataTable
    function isi(){
        $('#data-tabel').DataTable({
            lengthChange: true,
            processing: true,
            serverSide: true,
            ajax: `{{route('barang.list')}}`,
            columns: [
                {
                    "data": null,
                    "sortable": false,
                    render: function(data, type, row, meta){
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                { data: 'img', name: 'img' },
                { data: 'code', name: 'code' },
                { data: 'name', name: 'name' },
                { data: 'category_name', name: 'category_name' },
                { data: 'unit_name', name: 'unit_name' },
                { data: 'brand_name', name: 'brand_name' },
                { data: 'quantity_formatted', name: 'quantity_formatted' },
                {{-- Kolom action hanya untuk employee --}}
                @if(Auth::user()->role->name == 'employee')
                { data: 'tindakan', name: 'tindakan' }
                @endif
            ]
        });
    }

    // Form Validation
    function validateForm(){
        const name = $("input[name='nama']").val().trim();
        const category_id = $("select[name='jenisbarang']").val();
        const unit_id = $("select[name='satuan']").val();
        const brand_id = $("select[name='merk']").val();
        const quantity = $("input[name='jumlah']").val();

        if(!name){
            showAlert('warning', 'Nama barang tidak boleh kosong!');
            return false;
        }

        if(!category_id || !unit_id || !brand_id){
            showAlert('warning', 'Mohon lengkapi semua field yang wajib diisi!');
            return false;
        }

        if(quantity < 0){
            showAlert('warning', 'Jumlah stok tidak boleh minus!');
            return false;
        }

        return true;
    }

    // Show Alert
    function showAlert(type, message, timer = 1500){
        Swal.fire({
            position: "center",
            icon: type,
            title: message,
            showConfirmButton: timer > 1500,
            timer: timer
        });
    }

    // Create FormData
    function createFormData(){
        const formData = new FormData();
        const image = $("#GetFile")[0].files;
        
        if(image.length > 0) {
            formData.append('image', image[0]);
        }
        formData.append('code', $("input[name='kode']").val());
        formData.append('name', $("input[name='nama']").val());
        formData.append('category_id', $("select[name='jenisbarang']").val());
        formData.append('unit_id', $("select[name='satuan']").val());
        formData.append('brand_id', $("select[name='merk']").val());
        formData.append('quantity', $("input[name='jumlah']").val());
        formData.append('_token', '{{csrf_token()}}');
        
        if(isEditMode){
            formData.append('id', $("input[name='id']").val());
            formData.append('_method', 'PUT');
        }
        
        return formData;
    }

    // Save Item
    function simpan(){
        if(!validateForm()) return;

        const formData = createFormData();
        const url = isEditMode ? `{{route('barang.update')}}` : `{{route('barang.save')}}`;

        $.ajax({
            url: url,
            type: "post",
            processData: false,
            contentType: false,
            dataType: 'json',
            data: formData,
            success: function(res){
                showAlert('success', res.message);
                closeModal();
                reloadTable();
            },
            statusCode: {
                422: function(res) {
                    handleValidationErrors(res.responseJSON.errors);
                }
            },
            error: function(err){
                console.error(err);
                const message = err.responseJSON?.message || 'Terjadi kesalahan!';
                showAlert('error', message);
            }
        });
    }

    // Handle Validation Errors
    function handleValidationErrors(errors){
        let errorText = Object.values(errors).flat().join('\n');
        showAlert('warning', errorText, 3000);
    }

    // Reset Form
    function resetForm() {
        $("input[name='id']").val('');
        $("input[name='nama']").val('');
        $("input[name='kode']").val('');
        $("#GetFile").val('');
        $("#outputImg").attr('src', '{{asset("default.png")}}');
        $("select[name='jenisbarang']").val('');
        $("select[name='satuan']").val('');
        $("select[name='merk']").val('');
        $("input[name='jumlah']").val(0);
        $("#simpan").text("{{ __('save') }}");
        isEditMode = false;
    }

    // Close Modal
    function closeModal(){
        $('#kembali').click();
        resetForm();
    }

    // Reload Table
    function reloadTable(){
        $('#data-tabel').DataTable().ajax.reload();
    }

    // Image Preview
    function readURL(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#outputImg').attr('src', e.target.result);
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Generate Code
    function generateCode(){
        const timestamp = new Date().getTime();
        return "BRG-" + timestamp;
    }

    // Show Delete Modal
    function showDeleteModal(id, name){
        deleteItemId = id;
        $('#deleteItemName').text(name);
        $('#deleteModal').modal('show');
    }

    // Show Error Modal
    function showErrorModal(message){
        $('#errorModal .modal-body p').text(message);
        $('#errorModal').modal('show');
    }

    // Confirm Delete
    function confirmDelete(){
        if(!deleteItemId) return;

        $.ajax({
            url: "{{route('barang.delete')}}",
            type: "delete",
            data: {
                id: deleteItemId,
                "_token": "{{csrf_token()}}"
            },
            success: function(res){
                showAlert('success', res.message);
                $('#deleteModal').modal('hide');
                reloadTable();
                deleteItemId = null;
            },
            error: function(xhr){
                $('#deleteModal').modal('hide');
                
                if(xhr.status === 400){
                    // Show custom error modal for cannot delete
                    showErrorModal('Data ini tidak dapat dihapus dikarenakan digunakan pada data yang lain');
                } else {
                    const message = xhr.responseJSON?.message || 'Gagal menghapus data!';
                    showAlert('error', message);
                }
                deleteItemId = null;
            }
        });
    }

    // Event Handlers
    $(document).ready(function(){
        isi();

        // Image preview
        $("#GetFile").change(function() {
            readURL(this);
        });

        // Save button
        $('#simpan').on('click', function(){
            simpan();
        });

        // Modal button
        $("#modal-button").on("click", function(){
            resetForm();
            $("input[name='kode']").val(generateCode());
        });

        // Edit button
        $(document).on("click", ".ubah", function(){
            const id = $(this).attr('id');
            $("#modal-button").click();
            $("#simpan").text("{{ __('save changes') }}");
            isEditMode = true;
            
            $.ajax({
                url: "{{route('barang.detail')}}",
                type: "post",
                data: {
                    id: id,
                    "_token": "{{csrf_token()}}"
                },
                success: function(response){
                    const data = response.data;
                    $("input[name='id']").val(data.id);
                    $("input[name='nama']").val(data.name);
                    $("input[name='kode']").val(data.code);
                    $("select[name='jenisbarang']").val(data.category_id);
                    $("select[name='satuan']").val(data.unit_id);
                    $("select[name='merk']").val(data.brand_id);
                    $("input[name='jumlah']").val(data.quantity);
                    
                    if(data.image) {
                        $("#outputImg").attr('src', '{{asset("storage/barang/")}}/' + data.image);
                    }
                },
                error: function(err){
                    console.error(err);
                    const message = err.responseJSON?.message || 'Gagal mengambil data!';
                    showAlert('error', message);
                }
            });
        });

        // Delete button
        $(document).on("click", ".hapus", function(){
            const id = $(this).attr('id');
            const name = $(this).attr('data-name');
            showDeleteModal(id, name);
        });

        // Confirm delete button
        $('#confirmDelete').on('click', function(){
            confirmDelete();
        });
    });
</script>
@endsection