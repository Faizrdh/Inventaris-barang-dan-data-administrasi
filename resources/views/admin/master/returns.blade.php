@extends('layouts.app')
@section('title', __('returns'))
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

                <!-- Modal -->
                <div class="modal fade" id="TambahData" tabindex="-1" aria-labelledby="TambahDataModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="TambahDataModalLabel">{{ __("add return data") }}</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="form-group mb-3">
                                    <label for="borrower_id">{{ __("borrower") }}</label>
                                    <select class="form-control" id="borrower_id" autocomplete="off">
                                        <option value="">{{ __("select borrower") }}</option>
                                        @foreach($customers as $customer)
                                            <option value="{{$customer->id}}">{{$customer->name}}</option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="id" id="id">
                                </div>
                                <div class="form-group mb-3">
                                    <label for="item_code">{{ __("item") }}</label>
                                    <select class="form-control" id="item_code" autocomplete="off">
                                        <option value="">{{ __("select item") }}</option>
                                        @foreach($items as $item)
                                            <option value="{{$item->code}}">{{$item->name}} ({{$item->code}})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group mb-3">
                                    <label for="return_date">{{ __("return date") }}</label>
                                    <input type="date" class="form-control" id="return_date" autocomplete="off" value="{{ date('Y-m-d') }}">
                                </div>
                                <div class="form-group mb-3">
                                    <label for="status">{{ __("status") }}</label>
                                    <select class="form-control" id="status" autocomplete="off">
                                        <option value="">{{ __("select status") }}</option>
                                        <option value="Baik">{{ __("Good") }}</option>
                                        <option value="Rusak">{{ __("Damaged") }}</option>
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
                        <table id="data-returns" width="100%" class="table table-bordered text-nowrap border-bottom dataTable no-footer dtr-inline collapsed">
                            <thead>
                                <tr>
                                    <th class="border-bottom-0" width="8%">{{ __("no") }}</th>
                                    <th class="border-bottom-0">{{ __("borrower") }}</th>
                                    <th class="border-bottom-0">{{ __("item name") }}</th>
                                    <th class="border-bottom-0">{{ __("item code") }}</th>
                                    <th class="border-bottom-0">{{ __("return date") }}</th>
                                    <th class="border-bottom-0">{{ __("status") }}</th>
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
    function isi(){
        $('#data-returns').DataTable({
            responsive: true, lengthChange: true, autoWidth: false,
            processing: true,
            serverSide: true,
            ajax: `{{route('return.list')}}`,
            columns: [
                {
                    "data": null, "sortable": false,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    data: 'customer_name',
                    name: 'customer.name'
                },
                {
                    data: 'item_name',
                    name: 'item.name'
                },
                {
                    data: 'item_code',
                    name: 'item_code'
                },
                {
                    data: 'return_date',
                    name: 'return_date'
                },
                {
                    data: 'status',
                    name: 'status',
                    render: function(data) {
                        if (data == 'Baik') {
                            return "<span class='badge badge-success'>Baik</span>";
                        } else {
                            return "<span class='badge badge-danger'>Rusak</span>";
                        }
                    }
                },
                {{-- Kolom action hanya untuk employee --}}
                @if(Auth::user()->role->name == 'employee')
                {
                    data: 'tindakan',
                    name: 'tindakan'
                }
                @endif
            ]
        }).buttons().container();
    }

    function simpan(){
        if($('#borrower_id').val().length == 0){
            return Swal.fire({
                position: "center",
                icon: "warning",
                title: "borrower tidak boleh kosong !",
                showConfirmButton: false,
                timer: 1500
            });
        }
        if($('#item_code').val().length == 0){
            return Swal.fire({
                position: "center",
                icon: "warning",
                title: "item tidak boleh kosong !",
                showConfirmButton: false,
                timer: 1500
            });
        }
        if($('#return_date').val().length == 0){
            return Swal.fire({
                position: "center",
                icon: "warning",
                title: "return date tidak boleh kosong !",
                showConfirmButton: false,
                timer: 1500
            });
        }
        if($('#status').val().length == 0){
            return Swal.fire({
                position: "center",
                icon: "warning",
                title: "status tidak boleh kosong !",
                showConfirmButton: false,
                timer: 1500
            });
        }

        $.ajax({
            url: `{{route('return.save')}}`,
            type: "post",
            data: {
                borrower_id: $("#borrower_id").val(),
                item_code: $("#item_code").val(),
                return_date: $("#return_date").val(),
                status: $("#status").val(),
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
                $("#borrower_id").val('');
                $("#item_code").val('');
                $("#return_date").val('{{ date("Y-m-d") }}');
                $("#status").val('');
                $('#data-returns').DataTable().ajax.reload();
            },
            error: function(err){
                console.log(err.responseJSON.message);
            },
        });
    }

    function ubah(){
        if($('#borrower_id').val().length == 0){
            return Swal.fire({
                position: "center",
                icon: "warning",
                title: "borrower tidak boleh kosong !",
                showConfirmButton: false,
                timer: 1500
            });
        }
        if($('#item_code').val().length == 0){
            return Swal.fire({
                position: "center",
                icon: "warning",
                title: "item tidak boleh kosong !",
                showConfirmButton: false,
                timer: 1500
            });
        }
        if($('#return_date').val().length == 0){
            return Swal.fire({
                position: "center",
                icon: "warning",
                title: "return date tidak boleh kosong !",
                showConfirmButton: false,
                timer: 1500
            });
        }
        if($('#status').val().length == 0){
            return Swal.fire({
                position: "center",
                icon: "warning",
                title: "status tidak boleh kosong !",
                showConfirmButton: false,
                timer: 1500
            });
        }

        $.ajax({
            url: `{{route('return.update')}}`,
            type: "put",
            data: {
                id: $("#id").val(),
                borrower_id: $("#borrower_id").val(),
                item_code: $("#item_code").val(),
                return_date: $("#return_date").val(),
                status: $("#status").val(),
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
                $("#borrower_id").val('');
                $("#item_code").val('');
                $("#return_date").val('{{ date("Y-m-d") }}');
                $("#status").val('');
                $('#data-returns').DataTable().ajax.reload();
                $('#simpan').text('{{ __("save") }}');
            },
            error: function(err){
                console.log(err.responseJSON.message);
            },
        });
    }

    $(document).ready(function(){
        isi();

        $('#simpan').on('click', function(){
            if($(this).text() === '{{ __("save changes") }}'){
                ubah();
            } else {
                simpan();
            }
        });

        $("#modal-button").on("click", function(){
            $("#borrower_id").val('');
            $("#item_code").val('');
            $("#return_date").val('{{ date("Y-m-d") }}');
            $("#status").val('');
            $("#simpan").text("{{ __('save') }}");
        });
    });

    $(document).on("click", ".ubah", function(){
        let id = $(this).attr('id');
        $("#modal-button").click();
        $("#simpan").text("{{ __('save changes') }}");
        $.ajax({
            url: "{{route('return.detail')}}",
            type: "post",
            data: {
                id: id,
                "_token": "{{csrf_token()}}"
            },
            success: function({data}){
                $("#id").val(data.id);
                $("#borrower_id").val(data.borrower_id);
                $("#item_code").val(data.item_code);
                $("#return_date").val(data.return_date);
                $("#status").val(data.status);
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
            title: "Anda Yakin ?",
            text: "Data Ini Akan Di Hapus",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Ya,Hapus",
            cancelButtonText: "Tidak, Kembali!",
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{route('return.delete')}}",
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
                        $('#data-returns').DataTable().ajax.reload();
                    }
                });
            }
        });
    });
</script>

@endsection