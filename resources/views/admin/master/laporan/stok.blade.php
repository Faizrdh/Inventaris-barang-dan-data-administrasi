@extends('layouts.app')
@section('title',__("stock report"))
@section('content')

{{-- Semua user dapat mengakses halaman ini --}}
<x-head-datatable/>
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card w-100">
                <div class="card-header row">
                    <div class="row w-100">
                        <div class="col-lg-6  w-100">
                            <div class="row">
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="date_start">{{ __("start date") }}: </label>
                                        <input type="date" name="start_date" class="form-control w-100">
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="date_start">{{ __("end date") }}: </label>
                                         <input type="date" name="end_date" class="form-control w-100">
                                    </div>
                                </div>
                                <div class="col-sm-4 pt-4">
                                    <button class="btn btn-primary font-weight-bold m-1 mt-1" id="filter">{{ __("filter") }}</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6  w-100 d-flex justify-content-end align-items-center">
                                {{-- Button export hanya untuk admin --}}
                                @if(Auth::user()->role->name == 'admin' || Auth::user()->role_id === 1)
                                    <button class="btn btn-outline-primary font-weight-bold m-1" id="print">{{ __("print") }}</button>
                                    <button class="btn btn-outline-danger font-weight-bold m-1" id="export-pdf">{{ __("messages.export-to", ["file" => "pdf"]) }}</button>
                                    <button class="btn btn-outline-success font-weight-bold m-1" id="export-excel">{{ __("messages.export-to", ["file" => "excel"]) }}</button>
                                @endif
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="data-tabel" width="100%"  class="table table-bordered text-nowrap border-bottom dataTable no-footer dtr-inline collapsed">
                            <thead>
                                <tr>
                                    <th class="border-bottom-0" width="8%">{{ __("no") }}</th>
                                    <th class="border-bottom-0">{{__('item code')}}</th>
                                    <th class="border-bottom-0">{{__('item')}}</th>
                                    <th class="border-bottom-0">{{__('category')}}</th>
                                    <th class="border-bottom-0">{{__('first stock')}}</th>
                                    <th class="border-bottom-0">{{__('incoming amount')}}</th>
                                    <th class="border-bottom-0">{{__('outgoing amount')}}</th>
                                    <th class="border-bottom-0">{{__('stock amount')}}</th>
                                    <th class="border-bottom-0">{{__('status')}}</th>
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

    $(document).ready(function(){
        const tabel = $('#data-tabel').DataTable({
            lengthChange: true,
            processing:true,
            serverSide:true,
            ajax:{
                url:`{{route('laporan.stok.list')}}`,
                data:function(d){
                    d.start_date = $("input[name='start_date']").val();
                    d.end_date = $("input[name='end_date']").val();
                },
                error: function(xhr, error, thrown) {
                    console.error('DataTables error:', error, thrown);
                    
                    if (xhr.status === 403) {
                        alert('Akses tidak diizinkan. Anda akan diarahkan ke halaman utama.');
                        window.location.href = '/';
                    } else {
                        alert('Terjadi kesalahan saat memuat data. Silakan refresh halaman.');
                    }
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
                    data:"kode_barang",
                    name:"kode_barang"
                },
                {
                    data:"nama_barang",
                    name:"nama_barang"
                },
                {
                    data:"category",
                    name:"category"
                },
                {
                    data:"stok_awal_formatted",
                    name:"stok_awal",
                    render: function(data, type, row) {
                        if (typeof data !== 'undefined') {
                            return data;
                        }
                        return (row.stok_awal || 0) + ' ' + (row.unit || '');
                    }
                },
                {
                    data:"jumlah_masuk_formatted",
                    name:"jumlah_masuk",
                    render: function(data, type, row) {
                        if (typeof data !== 'undefined') {
                            return data;
                        }
                        return '<span class="text-success">' + (row.jumlah_masuk || 0) + ' ' + (row.unit || '') + '</span>';
                    }
                },
                {
                    data:"jumlah_keluar_formatted",
                    name:"jumlah_keluar",
                    render: function(data, type, row) {
                        if (typeof data !== 'undefined') {
                            return data;
                        }
                        return '<span class="text-warning">' + (row.jumlah_keluar || 0) + ' ' + (row.unit || '') + '</span>';
                    }
                },
                {
                    data:"total_formatted",
                    name:"total",
                    render: function(data, type, row) {
                        if (typeof data !== 'undefined') {
                            return data;
                        }
                        const total = row.total || 0;
                        const unit = row.unit || '';
                        const className = total <= 0 ? 'text-danger' : (total <= 3 ? 'text-warning' : 'text-success');
                        const icon = total <= 0 ? 'fas fa-times-circle' : (total <= 3 ? 'fas fa-exclamation-triangle' : 'fas fa-check-circle');
                        return '<span class="' + className + '"><i class="' + icon + ' mr-1"></i>' + total + ' ' + unit + '</span>';
                    }
                },
                {
                    data:"status_badge",
                    name:"status",
                    render: function(data, type, row) {
                        if (typeof data !== 'undefined') {
                            return data;
                        }
                        const stock = row.total || 0;
                        let status = 'normal';
                        if (stock <= 0) status = 'out_of_stock';
                        else if (stock <= 3) status = 'low_stock';
                        else if (stock > 10) status = 'high';
                        
                        const badges = {
                            'out_of_stock': '<span class="badge badge-danger"><i class="fas fa-times mr-1"></i>Habis</span>',
                            'low_stock': '<span class="badge badge-warning"><i class="fas fa-exclamation-triangle mr-1"></i>Rendah</span>',
                            'normal': '<span class="badge badge-success"><i class="fas fa-check mr-1"></i>Normal</span>',
                            'high': '<span class="badge badge-info"><i class="fas fa-arrow-up mr-1"></i>Tinggi</span>'
                        };
                        
                        return badges[status] || '<span class="badge badge-secondary">Unknown</span>';
                    }
                }
            ],
            buttons:[
                {
                    extend:'excel',
                    class:'buttons-excel'
                },
                {
                    extend:'print',
                    class:'buttons-print'
                },{
                    extend:'pdf',
                    class:'buttons-pdf'
                }
            ]
        });

        // Event handler untuk filter - tersedia untuk semua user
        $("#filter").on('click',function(){
            tabel.draw();
        });
        
        {{-- Event handler untuk button export hanya jika admin --}}
        @if(Auth::user()->role->name == 'admin' || Auth::user()->role_id === 1)
            $("#print").on('click',function(){
                tabel.button(".buttons-print").trigger();
            });
            $("#export-pdf").on('click',function(){
                tabel.button(".buttons-pdf").trigger();
            });
            $("#export-excel").on('click',function(){
                tabel.button(".buttons-excel").trigger();
            });
        @endif
    });
</script>

@endsection