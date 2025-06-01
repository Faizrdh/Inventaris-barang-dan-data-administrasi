@extends('layouts.app')
@section('title',__("laporan surat masuk"))
@section('content')
<x-head-datatable/>
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card w-100">
                <div class="card-header row">
                    <div class="row w-100">
                        <div class="col-lg-6 w-100">
                            <div class="row">
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="start_date">{{__("tanggal mulai")}}: </label>
                                        <input type="date" name="start_date" class="form-control w-100">
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="form-group">
                                        <label for="end_date">{{__("tanggal akhir")}}: </label>
                                         <input type="date" name="end_date" class="form-control w-100">
                                    </div>
                                </div>
                                <div class="col-sm-4 pt-4">
                                    <button class="btn btn-primary font-weight-bold m-1 mt-1" id="filter">
                                        <i class="fas fa-filter m-1"></i>{{ __("filter") }}
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 w-100 d-flex justify-content-end align-items-center">
                            <button class="btn btn-outline-primary font-weight-bold m-1" id="print">
                                <i class="fas fa-print m-1"></i>{{__("print")}}
                            </button>
                            <button class="btn btn-outline-danger font-weight-bold m-1" id="export-pdf">
                                <i class="fas fa-file-pdf m-1"></i>{{ __("messages.export-to", ["file" => "pdf"]) }}
                            </button>
                            <button class="btn btn-outline-success font-weight-bold m-1" id="export-excel">
                                <i class="fas fa-file-excel m-1"></i>{{ __("messages.export-to", ["file" => "excel"]) }}
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="data-tabel" width="100%" class="table table-bordered text-nowrap border-bottom dataTable no-footer dtr-inline collapsed">
                            <thead>
                                <tr>
                                    <th class="border-bottom-0" width="5%">{{__('no')}}</th>
                                    <th class="border-bottom-0" width="10%">{{__('tanggal diterima')}}</th>
                                    <th class="border-bottom-0" width="12%">{{__('kode surat')}}</th>
                                    <th class="border-bottom-0" width="20%">{{__('nama surat')}}</th>
                                    <th class="border-bottom-0" width="12%">{{__('kategori')}}</th>
                                    <th class="border-bottom-0" width="15%">{{__('pengirim')}}</th>
                                    <th class="border-bottom-0" width="13%">{{__('departemen')}}</th>
                                    <th class="border-bottom-0" width="8%">{{__('status file')}}</th>
                                    <th class="border-bottom-0" width="10%">{{__('diterima oleh')}}</th>
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
            processing: true,
            serverSide: true,
            ajax: {
                url: `{{route('laporan.surat-masuk.list')}}`,
                data: function(d) {
                    d.start_date = $("input[name='start_date']").val();
                    d.end_date = $("input[name='end_date']").val();
                }
            },
            columns: [
                {
                    "data": null,
                    "sortable": false,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    data: "received_date",
                    name: "received_date"
                },
                {
                    data: "letter_code",
                    name: "letter_code"
                },
                {
                    data: "letter_name",
                    name: "letter_name"
                },
                {
                    data: "category_name",
                    name: "category_name"
                },
                {
                    data: "sender_name",
                    name: "sender_name"
                },
                {
                    data: "from_department",
                    name: "from_department"
                },
                {
                    data: "file_status",
                    name: "file_status"
                },
                {
                    data: "received_by",
                    name: "received_by"
                }
            ],
            buttons: [
                {
                    extend: 'excel',
                    class: 'buttons-excel'
                },
                {
                    extend: 'print',
                    class: 'buttons-print'
                },
                {
                    extend: 'pdf',
                    class: 'buttons-pdf'
                }
            ]
        });

        $("#filter").on('click', function() {
            tabel.draw();
        });

        $("#print").on('click', function() {
            tabel.button(".buttons-print").trigger();
        });

        $("#export-pdf").on('click', function() {
            tabel.button(".buttons-pdf").trigger();
        });

        $("#export-excel").on('click', function() {
            tabel.button(".buttons-excel").trigger();
        });
    });
</script>
@endsection