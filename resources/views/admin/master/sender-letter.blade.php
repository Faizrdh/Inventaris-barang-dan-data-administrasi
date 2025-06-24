@extends('layouts.app')
@section('title', __("Pengirim Surat"))
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
                            <i class="fas fa-plus"></i> {{__("Tambah Data")}}
                        </button>
                        @endif
                    </div>
                </div>

                <!-- Modal -->
                <div class="modal fade" id="TambahData" tabindex="-1" aria-labelledby="TambahDataModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="TambahDataModalLabel">{{__('adding sender letter data')}}</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true" onclick="clearForm()">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="id" id="id">
                                <div class="form-group mb-3">
                                    <label for="from_department">{{__('asal surat')}} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="from_department" autocomplete="off" placeholder="Masukkan asal surat">
                                </div>
                                <div class="form-group mb-3">
                                    <label for="destination">{{__('tujuan')}} <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="destination" rows="3" placeholder="Masukkan tujuan surat"></textarea>
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
                        <table id="data-tabel" width="100%" class="table table-bordered text-nowrap border-bottom dataTable no-footer dtr-inline collapsed">
                            <thead>
                                <tr>
                                    <th class="border-bottom-0" width="4%">No</th>
                                    <th class="border-bottom-0">{{__('asal surat')}}</th>
                                    <th class="border-bottom-0">{{__('tujuan')}}</th>
                                    {{-- Kolom action hanya untuk employee --}}
                                    @if(Auth::user()->role->name == 'employee')
                                    <th class="border-bottom-0" width="1%">{{__('action')}}</th>
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
                data: 'from_department',
                name: 'from_department',
            },
            {
                data: 'destination',
                name: 'destination',
                render: function(data){
                    if(data == null || data == ''){
                        return "<span class='font-weight-bold text-muted'>-</span>";
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
            ajax: `{{route('sender_letter.list')}}`,
            columns: columns
        }).buttons().container();
    }

    // Form Validation
    function validateForm(){
        const from_department = $("#from_department").val().trim();
        const destination = $("#destination").val().trim();

        if(!from_department){
            showAlert('warning', 'Asal surat tidak boleh kosong!');
            return false;
        }

        if(!destination){
            showAlert('warning', 'Tujuan surat tidak boleh kosong!');
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

    function simpan(){
        if(!validateForm()) return;

        $.ajax({
            url: `{{route('sender_letter.save')}}`,
            type: "post",
            data: {
                from_department: $("#from_department").val().trim(),
                destination: $("#destination").val().trim(),
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
            url: `{{route('sender_letter.update')}}`,
            type: "put",
            data: {
                id: $("#id").val(),
                from_department: $("#from_department").val().trim(),
                destination: $("#destination").val().trim(),
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
        $("#from_department").val('');
        $("#destination").val('');
        $("#simpan").text("{{__('save')}}");
        $("#TambahDataModalLabel").text("{{__('adding sender letter data')}}");
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
        $("#TambahDataModalLabel").text("{{__('changing sender letter data')}}");
        $("#simpan").text("{{__('update')}}");
        
        $.ajax({
            url: "{{route('sender_letter.detail')}}",
            type: "post",
            data: {
                id: id,
                "_token": "{{csrf_token()}}"
            },
            success: function(response){
                const data = response.data;
                $("#id").val(data.id);
                $("#from_department").val(data.from_department);
                $("#destination").val(data.destination || '');
            },
            error: function(err){
                console.error(err);
                showAlert('error', 'Gagal mengambil data!');
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
                    url: "{{route('sender_letter.delete')}}",
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