@extends('layouts.app')
@section('title', __("messages.setting-label", ["name" => __("user")]))
@section('content')
<x-head-datatable/>
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card w-100">
                <div class="card-header row">
                    <div class="d-flex justify-content-end align-items-center w-100">
                        <button class="btn btn-success" type="button"  data-toggle="modal" data-target="#TambahData" id="modal-button"><i class="fas fa-plus m-1"></i>{{ __("add data") }}</button>
                    </div>
                </div>

                <!-- Modal -->
                <div class="modal fade" id="TambahData" tabindex="-1" aria-labelledby="TambahDataModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="TambahDataModalLabel">{{ __("add data") }}</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true" onclick="clear()" >&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group mb-3">
                                <label for="name">{{ __("name") }}</label>
                                <input type="text" class="form-control" id="name" autocomplete="off" required>
                                <input type="hidden" name="id" id="id">
                            </div>
                            <div class="form-group mb-3">
                                <label for="username">{{ __("username") }}</label>
                                <input type="text" class="form-control" id="username" autocomplete="off" required>
                            </div>
                            <div class="form-group mb-3">
                                <label for="password">{{ __("password") }}</label>
                                <input type="password" class="form-control" id="password" required>
                                <small class="form-text text-muted" id="password-help" style="display: none;">Kosongkan jika tidak ingin mengubah password</small>
                            </div>
                            <div class="form-group mb-3">
                                <label for="role">{{ __("role") }}</label>
                                <select class="form-control" id="role" required>
                                    <option value="">-- {{ __("role") }} --</option>
                                    @foreach($roles as $role)
                                        <option value="{{$role->id}}">{{$role->name}}</option>
                                    @endforeach
                                </select>
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
                        <table id="data-tabel" width="100%"  class="table table-bordered text-nowrap border-bottom dataTable no-footer dtr-inline collapsed">
                            <thead>
                                <tr>
                                    <th class="border-bottom-0" width="4%">{{ __("no") }}</th>
                                    <th class="border-bottom-0">{{ __("name") }}</th>
                                    <th class="border-bottom-0">{{ __("username") }}</th>
                                    <th class="border-bottom-0">{{ __("role") }}</th>
                                    <th class="border-bottom-0" width="1%">{{ __("action") }}</th>
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
    function isi(){
        $('#data-tabel').DataTable({
            responsive: true, lengthChange: true, autoWidth: false,
            processing:true,
            serverSide:true,
            ajax:`{{route('settings.employee.list')}}`,
            columns:[
                {
                    "data":null,"sortable":false,
                    render:function(data,type,row,meta){
                        return meta.row + meta.settings._iDisplayStart+1;
                    }
                },
                {
                    data:'name',
                    name:'name'
                },
                {
                    data:'username',
                    name:'username',
                },
                {
                    data:'role_name',
                    name:'role_name',
                },
                {
                    data:'tindakan',
                    name:'tindakan'
                }
            ]
        }).buttons().container();
    }

    function validateForm() {
        const name = $("#name").val().trim();
        const username = $("#username").val().trim();
        const password = $("#password").val().trim();
        const role = $("#role").val();
        const isUpdate = $("#simpan").text() === "{{__('update')}}";

        if (!name) {
            Swal.fire({
                position: "center",
                icon: "warning",
                title: "Nama tidak boleh kosong!",
                showConfirmButton: false,
                timer: 1500
            });
            return false;
        }

        if (!username) {
            Swal.fire({
                position: "center",
                icon: "warning",
                title: "Username tidak boleh kosong!",
                showConfirmButton: false,
                timer: 1500
            });
            return false;
        }

        if (!isUpdate && !password) {
            Swal.fire({
                position: "center",
                icon: "warning",
                title: "Password tidak boleh kosong!",
                showConfirmButton: false,
                timer: 1500
            });
            return false;
        }

        if (!role) {
            Swal.fire({
                position: "center",
                icon: "warning",
                title: "Role harus dipilih!",
                showConfirmButton: false,
                timer: 1500
            });
            return false;
        }

        return true;
    }

    function clearForm() {
        $("#id").val('');
        $("#name").val('');
        $("#username").val('');
        $("#password").val('');
        $("#role").val('');
    }

    function simpan(){
        if (!validateForm()) return;

        $.ajax({
            url:`{{route('settings.employee.save')}}`,
            type:"post",
            data:{
                name:$("#name").val(),
                username:$("#username").val(),
                password:$("#password").val(),
                role_id:$("#role").val(),
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
                clearForm();
                $('#data-tabel').DataTable().ajax.reload();
            },
            error:function(err){
                console.log(err);
                let errorMessage = "Terjadi kesalahan!";
                if (err.responseJSON && err.responseJSON.message) {
                    errorMessage = err.responseJSON.message;
                }
                Swal.fire({
                    position: "center",
                    icon: "error",
                    title: errorMessage,
                    showConfirmButton: false,
                    timer: 2000
                });
            },
        });
    }

    function ubah(){
        if (!validateForm()) return;

        $.ajax({
            url:`{{route('settings.employee.update')}}`,
            type:"put",
            data:{
                id:$("#id").val(),
                name:$("#name").val(),
                username:$("#username").val(),
                password:$("#password").val(),
                role_id:$("#role").val(),
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
                clearForm();
                $('#data-tabel').DataTable().ajax.reload();
                $('#simpan').text("{{__('save')}}");
                $('#password').attr('required', true);
                $('#password-help').hide();
            },
            error:function(err){
                console.log(err);
                let errorMessage = "Terjadi kesalahan!";
                if (err.responseJSON && err.responseJSON.message) {
                    errorMessage = err.responseJSON.message;
                }
                Swal.fire({
                    position: "center",
                    icon: "error",
                    title: errorMessage,
                    showConfirmButton: false,
                    timer: 2000
                });
            },
        });
    }

    $(document).ready(function(){
        isi();

        $('#simpan').on('click',function(){
            if($(this).text() === "{{__('update')}}"){
                ubah();
            }else{
                simpan();
            }
        });

        $("#modal-button").on("click",function(){
            clearForm();
            $("#simpan").text("{{__('save')}}");
            $("#TambahDataModalLabel").text("{{__('add data')}}");
            $('#password').attr('required', true);
            $('#password-help').hide();
        });
    });

    $(document).on("click",".ubah",function(){
        let id = $(this).attr('id');
        $("#modal-button").click();
        $("#simpan").text("{{__('update')}}");
        $("#TambahDataModalLabel").text("Ubah Profile Staff");
        $('#password').attr('required', false);
        $('#password-help').show();
        
        $.ajax({
            url:"{{route('settings.employee.detail')}}",
            type:"post",
            data:{
                id:id,
                "_token":"{{csrf_token()}}"
            },
            success:function(response){
                const data = response.data;
                $("#id").val(data.id);
                $("#name").val(data.name);
                $("#username").val(data.username);
                $("#role").val(data.role_id);
                $("#password").val(''); // Clear password field for edit
            },
            error:function(err){
                console.log(err);
                Swal.fire({
                    position: "center",
                    icon: "error",
                    title: "Gagal mengambil data user!",
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
                    url:"{{route('settings.employee.delete')}}",
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
                    error:function(err){
                        console.log(err);
                        let errorMessage = "Gagal menghapus data!";
                        if (err.responseJSON && err.responseJSON.message) {
                            errorMessage = err.responseJSON.message;
                        }
                        Swal.fire({
                            position: "center",
                            icon: "error",
                            title: errorMessage,
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