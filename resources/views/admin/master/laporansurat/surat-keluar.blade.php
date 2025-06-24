@extends('layouts.app')
@section('title',__("laporan surat keluar"))
@section('content')

{{-- Semua user dapat mengakses halaman ini --}}
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
                            {{-- Button export hanya untuk admin --}}
                            @if(Auth::user()->role->name == 'admin' || Auth::user()->role_id === 1)
                                <button class="btn btn-outline-primary font-weight-bold m-1" id="print">
                                    <i class="fas fa-print m-1"></i>{{__("print")}}
                                </button>
                                <button class="btn btn-outline-danger font-weight-bold m-1" id="export-pdf">
                                    <i class="fas fa-file-pdf m-1"></i>{{ __("messages.export-to", ["file" => "pdf"]) }}
                                </button>
                                <button class="btn btn-outline-success font-weight-bold m-1" id="export-excel">
                                    <i class="fas fa-file-excel m-1"></i>{{ __("messages.export-to", ["file" => "excel"]) }}
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="data-tabel" width="100%" class="table table-bordered text-nowrap border-bottom dataTable no-footer dtr-inline collapsed">
                            <thead>
                                <tr>
                                    <th class="border-bottom-0" width="5%">{{__('no')}}</th>
                                    <th class="border-bottom-0" width="10%">{{__('tanggal dikirim')}}</th>
                                    <th class="border-bottom-0" width="12%">{{__('kode surat')}}</th>
                                    <th class="border-bottom-0" width="15%">{{__('nama surat')}}</th>
                                    <th class="border-bottom-0" width="15%">{{__('perihal')}}</th>
                                    <th class="border-bottom-0" width="13%">{{__('tujuan')}}</th>
                                    <th class="border-bottom-0" width="15%">{{__('keterangan')}}</th>
                                    <th class="border-bottom-0" width="8%">{{__('status file')}}</th>
                                    <th class="border-bottom-0" width="10%">{{__('dikirim oleh')}}</th>
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
                url: "{{route('laporan.surat-keluar.list')}}",
                type: "POST",
                data: function(d) {
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
            columns: [
                {
                    "data": null,
                    "sortable": false,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    data: "sent_date",
                    name: "sent_date"
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
                    data: "perihal",
                    name: "perihal"
                },
                {
                    data: "tujuan",
                    name: "tujuan"
                },
                {
                    data: "keterangan",
                    name: "keterangan"
                },
                {
                    data: "file_status",
                    name: "file_status",
                    render: function(data, type, row) {
                        if (data === 'Ada File') {
                            return '<span class="badge badge-success"><i class="fas fa-file mr-1"></i>' + data + '</span>';
                        } else {
                            return '<span class="badge badge-secondary"><i class="fas fa-file-o mr-1"></i>' + data + '</span>';
                        }
                    }
                },
                {
                    data: "sent_by",
                    name: "sent_by"
                }
            ],
            @if(Auth::user()->role->name == 'admin' || Auth::user()->role_id === 1)
            buttons: [
                {
                    extend: 'excel',
                    className: 'buttons-excel',
                    title: 'Laporan Surat Keluar',
                    filename: function() {
                        const startDate = $("input[name='start_date']").val() || 'semua';
                        const endDate = $("input[name='end_date']").val() || 'semua';
                        return `laporan-surat-keluar-${startDate}-sampai-${endDate}`;
                    },
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8]
                    }
                },
                {
                    extend: 'print',
                    className: 'buttons-print',
                    title: 'Laporan Surat Keluar',
                    messageTop: function() {
                        const startDate = $("input[name='start_date']").val();
                        const endDate = $("input[name='end_date']").val();
                        if (startDate && endDate) {
                            return `Periode: ${startDate} sampai ${endDate}`;
                        }
                        return 'Periode: Semua Data';
                    },
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8]
                    }
                },
                {
                    extend: 'pdf',
                    className: 'buttons-pdf',
                    title: 'Laporan Surat Keluar',
                    filename: function() {
                        const startDate = $("input[name='start_date']").val() || 'semua';
                        const endDate = $("input[name='end_date']").val() || 'semua';
                        return `laporan-surat-keluar-${startDate}-sampai-${endDate}`;
                    },
                    messageTop: function() {
                        const startDate = $("input[name='start_date']").val();
                        const endDate = $("input[name='end_date']").val();
                        if (startDate && endDate) {
                            return `Periode: ${startDate} sampai ${endDate}`;
                        }
                        return 'Periode: Semua Data';
                    },
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7, 8]
                    },
                    customize: function(doc) {
                        // Customize PDF layout
                        doc.defaultStyle.fontSize = 8;
                        doc.styles.tableHeader.fontSize = 9;
                        doc.styles.title.fontSize = 14;
                        doc.styles.title.alignment = 'center';
                        
                        // Set page orientation to landscape for better table display
                        doc.pageOrientation = 'landscape';
                        doc.pageMargins = [20, 60, 20, 30];
                        
                        // Add header
                        doc.content.splice(0, 1, {
                            text: [
                                { text: 'LAPORAN SURAT KELUAR\n', fontSize: 16, bold: true, alignment: 'center' },
                                { text: doc.content[0].text, fontSize: 10, alignment: 'center' }
                            ],
                            margin: [0, 0, 0, 20]
                        });
                    }
                }
            ],
            dom: 'lBfrtip', // This includes the buttons in the DOM for admin
            @else
            dom: 'lfrtip', // No buttons for non-admin users
            @endif
            language: {
                processing: "Memproses...",
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data per halaman",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                infoFiltered: "(disaring dari _MAX_ total data)",
                loadingRecords: "Memuat...",
                zeroRecords: "Tidak ada data yang ditemukan",
                emptyTable: "Tidak ada data tersedia",
                paginate: {
                    first: "Pertama",
                    previous: "Sebelumnya",
                    next: "Selanjutnya",
                    last: "Terakhir"
                }
            }
        });

        // Event handler untuk filter - tersedia untuk semua user
        $("#filter").on('click', function() {
            tabel.draw();
        });

        {{-- Event handler untuk button export hanya jika admin --}}
        @if(Auth::user()->role->name == 'admin' || Auth::user()->role_id === 1)
            $("#print").on('click', function() {
                tabel.button(".buttons-print").trigger();
            });

            $("#export-pdf").on('click', function() {
                tabel.button(".buttons-pdf").trigger();
            });

            $("#export-excel").on('click', function() {
                tabel.button(".buttons-excel").trigger();
            });
        @endif
    });
</script>

@endsection