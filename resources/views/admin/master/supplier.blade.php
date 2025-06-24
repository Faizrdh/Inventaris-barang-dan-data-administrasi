@extends('layouts.app')
@section('title',__('Distributor'))
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
                        <button class="btn btn-success" type="button"  data-toggle="modal" data-target="#TambahData" id="modal-button"><i class="fas fa-plus"></i> {{__('tambah distributor')}}</button>
                    @endif
                    </div>
                </div>

                <!-- Modal -->
                <div class="modal fade" id="TambahData" tabindex="-1" aria-labelledby="TambahDataModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="TambahDataModalLabel">{{__('Menambahkan data distributor')}}</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true" onclick="clearForm()">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="name">{{__('name')}} <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="name" autocomplete="off" placeholder="Masukkan nama distributor">
                                        <input type="hidden" name="id" id="id">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="phone_number">{{__('phone number')}} <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="phone_number" autocomplete="off" placeholder="Masukkan nomor telepon">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="email">{{__('email')}}</label>
                                        <input type="email" class="form-control" id="email" autocomplete="off" placeholder="Masukkan email (opsional)">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="address">{{__('address')}} <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="address" rows="3" placeholder="Masukkan alamat distributor"></textarea>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="website">{{__('website')}}</label>
                                        <input type="url" class="form-control" id="website" autocomplete="off" placeholder="Masukkan website (opsional)">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal" id="kembali">{{__('cancel')}}</button>
                            <button type="button" class="btn btn-success" id="simpan">{{__('save')}}</button>
                        </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table id="data-tabel" width="100%"  class="table table-bordered text-nowrap border-bottom dataTable no-footer dtr-inline collapsed">
                            <thead>
                                <tr>
                                    <th class="border-bottom-0" width="4%">No</th>
                                    <th class="border-bottom-0">Nama</th>
                                    <th class="border-bottom-0">Nomor HP</th>
                                    <th class="border-bottom-0">Alamat</th>
                                    <th class="border-bottom-0">Email</th>
                                    <th class="border-bottom-0">Website</th>
                                    {{-- Kolom action hanya untuk employee --}}
                                    @if(Auth::user()->role->name == 'employee')
                                    <th class="border-bottom-0" width="1%">Tindakan</th>
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
    // Global Variables
    const userRole = "{{ Auth::user()->role->name }}";

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
                data: 'name',
                name: 'name'
            },
            {
                data: 'phone_number',
                name: 'phone_number',
            },
            {
                data: 'address',
                name: 'address',
                render: function(data){
                    if(data == null || data == ''){
                        return "<span class='font-weight-bold text-muted'>-</span>";
                    }
                    return data;
                }
            },
            {
                data: 'email',
                name: 'email',
                render: function(data){
                    if(data == null || data == ''){
                        return "<span class='font-weight-bold text-muted'>-</span>";
                    }
                    return data;
                }
            },
            {
                data: 'website',
                name: 'website',
                render: function(data){
                    if(data == null || data == ''){
                        return "<span class='font-weight-bold text-muted'>-</span>";
                    }
                    // Jika ada website, buat link
                    if(data && data.length > 0) {
                        const url = data.startsWith('http') ? data : 'https://' + data;
                        return `<a href="${url}" target="_blank" class="text-primary">${data}</a>`;
                    }
                    return data;
                }
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
            ajax: `{{route('supplier.list')}}`,
            columns: columns
        }).buttons().container();
    }

    // Form Validation
    function validateForm(){
        const name = $("#name").val().trim();
        const phone_number = $("#phone_number").val().trim();
        const address = $("#address").val().trim();
        const email = $("#email").val().trim();
        const website = $("#website").val().trim();

        if(!name){
            showAlert('warning', 'Nama distributor tidak boleh kosong!');
            return false;
        }

        if(!phone_number){
            showAlert('warning', 'Nomor telepon tidak boleh kosong!');
            return false;
        }

        if(!address){
            showAlert('warning', 'Alamat tidak boleh kosong!');
            return false;
        }

        // Validasi email jika diisi
        if(email && !isValidEmail(email)){
            showAlert('warning', 'Format email tidak valid!');
            return false;
        }

        // Validasi website jika diisi
        if(website && !isValidUrl(website)){
            showAlert('warning', 'Format website tidak valid!');
            return false;
        }

        return true;
    }

    // Email validation
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    // URL validation
    function isValidUrl(string) {
        try {
            // Add protocol if missing
            if (!string.startsWith('http://') && !string.startsWith('https://')) {
                string = 'https://' + string;
            }
            new URL(string);
            return true;
        } catch (_) {
            return false;
        }
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

    function simpan(){
        if(!validateForm()) return;

        $.ajax({
            url: `{{route('supplier.save')}}`,
            type: "post",
            data: {
                name: $("#name").val().trim(),
                phone_number: $("#phone_number").val().trim(),
                address: $("#address").val().trim(),
                email: $("#email").val().trim(),
                website: $("#website").val().trim(),
                "_token": "{{csrf_token()}}"
            },
            beforeSend: function() {
                $('#simpan').prop('disabled', true).text('Saving...');
            },
            success: function(res){
                showAlert('success', res.message);
                closeModal();
                $('#data-tabel').DataTable().ajax.reload();
            },
            error: function(err){
                console.error(err);
                let message = 'Terjadi kesalahan!';
                if(err.responseJSON && err.responseJSON.message){
                    message = err.responseJSON.message;
                }
                showAlert('error', message);
            },
            complete: function() {
                $('#simpan').prop('disabled', false).text('{{__("save")}}');
            }
        });
    }

    function ubah(){
        if(!validateForm()) return;

        $.ajax({
            url: `{{route('supplier.update')}}`,
            type: "put",
            data: {
                id: $("#id").val(),
                name: $("#name").val().trim(),
                phone_number: $("#phone_number").val().trim(),
                address: $("#address").val().trim(),
                email: $("#email").val().trim(),
                website: $("#website").val().trim(),
                "_token": "{{csrf_token()}}"
            },
            beforeSend: function() {
                $('#simpan').prop('disabled', true).text('Updating...');
            },
            success: function(res){
                showAlert('success', res.message);
                closeModal();
                $('#data-tabel').DataTable().ajax.reload();
            },
            error: function(err){
                console.error(err);
                let message = 'Terjadi kesalahan!';
                if(err.responseJSON && err.responseJSON.message){
                    message = err.responseJSON.message;
                }
                showAlert('error', message);
            },
            complete: function() {
                $('#simpan').prop('disabled', false).text('{{__("update")}}');
            }
        });
    }

    // Clear Form Function
    function clearForm(){
        $("#id").val('');
        $("#name").val('');
        $("#phone_number").val('');
        $("#address").val('');
        $("#email").val('');
        $("#website").val('');
        $("#simpan").text("{{__('save')}}");
        $("#TambahDataModalLabel").text("{{__('Menambahkan data distributor')}}");
    }

    // Close Modal
    function closeModal(){
        $('#kembali').click();
        clearForm();
    }

    $(document).ready(function(){
        isi();

        $('#simpan').on('click', function(){
            if($(this).text() === "{{__('update')}}"){
                ubah();
            } else {
                simpan();
            }
        });

        $("#modal-button").on("click", function(){
            clearForm();
        });

        // Reset modal when closed
        $('#TambahData').on('hidden.bs.modal', function () {
            clearForm();
        });
    });

    $(document).on("click", ".ubah", function(){
        let id = $(this).attr('id');
        $("#modal-button").click();
        $("#TambahDataModalLabel").text("{{__('merubah data distributor')}}");
        $("#simpan").text("{{__('update')}}");
        
        $.ajax({
            url: "{{route('supplier.detail')}}",
            type: "post",
            data: {
                id: id,
                "_token": "{{csrf_token()}}"
            },
            success: function(response){
                const data = response.data;
                $("#id").val(data.id);
                $("#name").val(data.name);
                $("#phone_number").val(data.phone_number);
                $("#address").val(data.address || '');
                $("#website").val(data.website || '');
                $("#email").val(data.email || '');
            },
            error: function(err){
                console.error(err);
                showAlert('error', 'Gagal mengambil data distributor!');
            }
        });
    });

    $(document).on("click", ".hapus", function(){
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
                    url: "{{route('supplier.delete')}}",
                    type: "delete",
                    data: {
                        id: id,
                        "_token": "{{csrf_token()}}"
                    },
                    success: function(res){
                        showAlert('success', res.message);
                        $('#data-tabel').DataTable().ajax.reload();
                    },
                    error: function(err){
                        console.error(err);
                        let message = 'Gagal menghapus data!';
                        if(err.responseJSON && err.responseJSON.message){
                            message = err.responseJSON.message;
                        }
                        showAlert('error', message);
                    }
                });
            }
        });
    });
</script>
@endsection