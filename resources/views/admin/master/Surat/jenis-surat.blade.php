@extends('layouts.app')
@section('title', __("Jenis Surat"))
@section('content')
<x-head-datatable/>
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card w-100">
                <div class="card-header row">
                    <div class="d-flex justify-content-end align-items-center w-100">
                        @if(Auth::user()->role->name != 'staff')
                        <button class="btn btn-success" type="button" data-toggle="modal" data-target="#TambahData" id="modal-button">
                            <i class="fas fa-plus"></i> {{ __("add data") }}
                        </button>
                        @endif
                    </div>
                </div>

                <!-- Modal -->
                <div class="modal fade" id="TambahData" tabindex="-1" aria-labelledby="TambahDataModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="TambahDataModalLabel">{{ __("add category letter") }}</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="form-group mb-3">
                                    <label for="name">{{ __("name") }} <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" autocomplete="off" placeholder="Masukan Jenis Surat">
                                    <input type="hidden" name="id" id="id">
                                </div>
                                <div class="form-group mb-3">
                                    <label for="desc">{{ __("description") }}</label>
                                    <textarea class="form-control" id="desc" rows="3" placeholder="Masukan Keterangan (optional)"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal" id="kembali">{{ __("back") }}</button>
                                <button type="button" class="btn btn-success" id="simpan">{{ __("save") }}</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table id="data-category-letter" width="100%" class="table table-bordered text-nowrap border-bottom dataTable no-footer dtr-inline collapsed">
                            <thead>
                                <tr>
                                    <th class="border-bottom-0" width="8%">{{ __("no") }}</th>
                                    <th class="border-bottom-0">{{ __("name") }}</th>
                                    <th class="border-bottom-0">{{ __("description") }}</th>
                                    @if(Auth::user()->role->name != 'staff')
                                    <th class="border-bottom-0" width="20%">{{ __("action") }}</th>
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
    // Kirim role user ke JS
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
                data: 'description',
                name: 'description',
                render: function(data){
                    if(data == null || data == ''){
                        return "<span class='font-weight-bold text-muted'>-</span>";
                    }
                    return data;
                }
            }
        ];

        if(userRole !== 'staff'){
            columns.push({
                data: 'tindakan',
                name: 'tindakan',
                orderable: false,
                searchable: false
            });
        }

        $('#data-category-letter').DataTable({
            responsive: true,
            lengthChange: true,
            autoWidth: false,
            processing: true,
            serverSide: true,
            ajax: `{{route('letter.category.list')}}`,
            columns: columns
        }).buttons().container();
    }

    function simpan(){
        if($('#name').val().trim().length == 0){
            return Swal.fire({
                position: "center",
                icon: "warning",
                title: "Name cannot be empty!",
                showConfirmButton: false,
                timer: 1500
            });
        }

        $('#simpan').prop('disabled', true);

        $.ajax({
            url: `{{route('letter.category.save')}}`,
            type: "post",
            data: {
                name: $("#name").val().trim(),
                description: $("#desc").val().trim(),
                "_token": "{{csrf_token()}}"
            },
            success: function(res){
                Swal.fire({
                    position: "center",
                    icon: "success",
                    title: res.message,
                    showConfirmButton: false,
                    timer: 1500
                });

                $('#kembali').click();
                resetForm();
                $('#data-category-letter').DataTable().ajax.reload();
            },
            error: function(err){
                let message = 'An error occurred';
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
            },
            complete: function(){
                $('#simpan').prop('disabled', false);
            }
        });
    }

    function ubah(){
        if($('#name').val().trim().length == 0){
            return Swal.fire({
                position: "center",
                icon: "warning",
                title: "Name cannot be empty!",
                showConfirmButton: false,
                timer: 1500
            });
        }

        $('#simpan').prop('disabled', true);

        $.ajax({
            url: `{{route('letter.category.update')}}`,
            type: "put",
            data: {
                id: $("#id").val(),
                name: $("#name").val().trim(),
                description: $("#desc").val().trim(),
                "_token": "{{csrf_token()}}"
            },
            success: function(res){
                Swal.fire({
                    position: "center",
                    icon: "success",
                    title: res.message,
                    showConfirmButton: false,
                    timer: 1500
                });

                $('#kembali').click();
                resetForm();
                $('#data-category-letter').DataTable().ajax.reload();
            },
            error: function(err){
                let message = 'An error occurred';
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
            },
            complete: function(){
                $('#simpan').prop('disabled', false);
            }
        });
    }

    function resetForm(){
        $("#id").val('');
        $("#name").val('');
        $("#desc").val('');
        $("#simpan").text("{{ __('save') }}");
    }

    $(document).ready(function(){
        isi();

        $('#simpan').on('click', function(){
            if($(this).text().includes('Changes') || $(this).text().includes('Update')){
                ubah();
            } else {
                simpan();
            }
        });

        $("#modal-button").on("click", function(){
            resetForm();
        });

        $('#TambahData').on('hidden.bs.modal', function () {
            resetForm();
        });
    });

    $(document).on("click", ".ubah", function(){
        let id = $(this).attr('id');
        $("#modal-button").click();
        $("#simpan").text("{{ __('save') }} Changes");

        $.ajax({
            url: "{{route('letter.category.detail')}}",
            type: "post",
            data: {
                id: id,
                "_token": "{{csrf_token()}}"
            },
            success: function(response){
                if(response.data){
                    $("#id").val(response.data.id);
                    $("#name").val(response.data.name);
                    $("#desc").val(response.data.description || '');
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
            title: "Are You Sure?",
            text: "This Data Will Be Deleted Permanently!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, Delete",
            cancelButtonText: "No, Cancel!",
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{route('letter.category.delete')}}",
                    type: "delete",
                    data: {
                        id: id,
                        "_token": "{{csrf_token()}}"
                    },
                    success: function(res){
                        Swal.fire({
                            position: "center",
                            icon: "success",
                            title: res.message,
                            showConfirmButton: false,
                            timer: 1500
                        });
                        $('#data-category-letter').DataTable().ajax.reload();
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
