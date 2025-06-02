@extends('layouts.app')
@section('title',__("Laporan Barang Masuk"))
@section('content')

{{-- Cek role admin seperti pada employee page --}}
@if(Auth::user()->role->name == 'admin' || Auth::user()->role_id === 1)
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
                                            <label for="date_start">{{__("start date")}}: </label>
                                            <input type="date" name="start_date" class="form-control w-100">
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label for="date_start">{{__("end date")}}: </label>
                                             <input type="date" name="end_date" class="form-control w-100">
                                        </div>
                                    </div>
                                    <div class="col-sm-4 pt-4">
                                        <button class="btn btn-primary font-weight-bold m-1 mt-1" id="filter"><i class="fas fa-filter m-1"></i>{{ __("filter") }}</button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6  w-100 d-flex justify-content-end align-items-center">
                                    <button class="btn btn-outline-primary font-weight-bold m-1" id="print"><i class="fas fa-print m-1"></i>{{__("print")}}</button>
                                    <button class="btn btn-outline-danger font-weight-bold m-1" id="export-pdf"><i class="fas fa-file-pdf m-1"></i>{{ __("messages.export-to", ["file" => "pdf"]) }}</button>
                                    <button class="btn btn-outline-success font-weight-bold m-1" id="export-excel"><i class="fas fa-file-excel m-1"></i>{{ __("messages.export-to", ["file" => "excel"]) }}</button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="data-tabel" width="100%"  class="table table-bordered text-nowrap border-bottom dataTable no-footer dtr-inline collapsed">
                                <thead>
                                    <tr>
                                        <th class="border-bottom-0" width="8%">{{__('no')}}</th>
                                        <th class="border-bottom-0">{{__('date')}}</th>
                                        <th class="border-bottom-0">{{__('incoming item code')}}</th>
                                        <th class="border-bottom-0">{{__('item code')}}</th>
                                        <th class="border-bottom-0">{{__('supplier')}}</th>
                                        <th class="border-bottom-0">{{__('item')}}</th>
                                        <th class="border-bottom-0">{{__('incoming amount')}}</th>
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
        // Function isi() seperti pada employee page
        function isi(){
            $('#data-tabel').DataTable({
                responsive: true, 
                lengthChange: true, 
                autoWidth: false,
                processing:true,
                serverSide:true,
                ajax:{
                    url:`{{route('laporan.masuk.list')}}`,
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
                    data:"date_received",
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
                    data:"quantity",
                    name:"quantity"
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
        }

        $(document).ready(function(){
            // Setup CSRF seperti employee page
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Initialize datatable
            isi();

            // Event handlers seperti code original
            $("#filter").on('click',function(){
                $('#data-tabel').DataTable().ajax.reload();
            });
            
            $("#print").on('click',function(){
                $('#data-tabel').DataTable().button(".buttons-print").trigger();
            });
            
            $("#export-pdf").on('click',function(){
                $('#data-tabel').DataTable().button(".buttons-pdf").trigger();
            });
            
            $("#export-excel").on('click',function(){
                $('#data-tabel').DataTable().button(".buttons-excel").trigger();
            });
        });
    </script>

@else
    {{-- Unauthorized Access Message seperti pada employee page --}}
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card border-danger">
                    <div class="card-header bg-danger text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Akses Ditolak
                        </h4>
                    </div>
                    <div class="card-body text-center">
                        <div class="mb-4">
                            <i class="fas fa-lock fa-4x text-danger mb-3"></i>
                            <h5 class="text-danger">Anda tidak memiliki izin untuk mengakses halaman ini</h5>
                            <p class="text-muted">
                                Halaman laporan barang masuk hanya dapat diakses oleh Administrator.
                            </p>
                        </div>
                        <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                            <a href="{{ url('/') }}" class="btn btn-primary">
                                <i class="fas fa-home me-1"></i>
                                Kembali ke Beranda
                            </a>
                            <a href="javascript:history.back()" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>
                                Kembali
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

@endsection