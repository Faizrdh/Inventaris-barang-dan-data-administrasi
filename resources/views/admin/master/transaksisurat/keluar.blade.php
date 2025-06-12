@extends('layouts.app')
@section('title',__("Surat Keluar"))
@section('content')
<x-head-datatable/>
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card w-100">
                <div class="card-header row">
                    <div class="d-flex justify-content-end align-items-center w-100">
                        <button class="btn btn-success" type="button" data-toggle="modal" data-target="#TambahData" id="modal-button">
                            <i class="fas fa-plus m-1"></i> {{__('add data')}}
                        </button>
                    </div>
                </div>

                <!-- Modal Pilih Surat -->
                <div class="modal fade" id="modal-letter" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-scrollable">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="staticBackdropLabel">{{__('select letter')}}</h5>
                                <button type="button" class="close" id="close-modal-letter">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="search-letter">Cari Surat:</label>
                                        <input type="text" id="search-letter" class="form-control" placeholder="Ketik kode atau nama surat...">
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table id="data-letter" width="100%" class="table table-bordered text-nowrap border-bottom">
                                        <thead>
                                            <tr>
                                                <th class="border-bottom-0" width="8%">{{__('no')}}</th>
                                                <th class="border-bottom-0">{{__('letter code')}}</th>
                                                <th class="border-bottom-0">{{__('letter name')}}</th>
                                                <th class="border-bottom-0">{{__('sender')}}</th>
                                                <th class="border-bottom-0">{{__('department')}}</th>
                                                <th class="border-bottom-0">{{__('category')}}</th>
                                                <th class="border-bottom-0">{{__('file status')}}</th>
                                                <th class="border-bottom-0">{{__('file info')}}</th>
                                                <th class="border-bottom-0" width="1%">{{__('action')}}</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Input/Edit -->
                <div class="modal fade" id="TambahData" tabindex="-1" aria-labelledby="TambahDataModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="TambahDataModalLabel">{{__('Membuat data surat keluar')}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="sent_date" class="form-label">{{__('tanggal keluar')}} <span class="text-danger">*</span></label>
                            <input type="date" name="sent_date" class="form-control">
                            <input type="hidden" name="id"/>
                            <input type="hidden" name="letter_id"/>
                            <input type="hidden" name="file_name"/>
                            <input type="hidden" name="file_path"/>
                            <input type="hidden" name="file_size"/>
                            <input type="hidden" name="file_type"/>
                        </div>
                        
                        <div class="form-group">
                            <label for="letter_code" class="form-label">{{__('Nomor surat')}} <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" name="letter_code" class="form-control" placeholder="Nomor surat">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-primary" type="button" id="cari-letter" title="Cari berdasarkan kode">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    <button class="btn btn-success" type="button" id="pilih-letter" title="Pilih dari daftar">
                                        <i class="fas fa-list"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="letter_name" class="form-label">{{__("Nama surat")}}</label>
                            <input type="text" name="letter_name" readonly class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label for="perihal" class="form-label">{{__('Perihal')}} <span class="text-danger">*</span></label>
                            <input type="text" name="perihal" class="form-control" placeholder="Perihal surat">
                        </div>

                        <!-- FIELD BARU: TUJUAN -->
                        <div class="form-group">
                            <label for="tujuan" class="form-label">{{__('Tujuan')}} <span class="text-danger">*</span></label>
                            <input type="text" name="tujuan" class="form-control" placeholder="Tujuan surat (Institusi/Departemen/Person)">
                        </div>

                        <!-- File Information Display - DIKECILKAN -->
                        <div class="form-group">
                            <label class="form-label">{{__('File Information')}}</label>
                            <div id="file-info-display" class="border rounded p-2 bg-light" style="display: none;">
                                <div class="d-flex align-items-center mb-1">
                                    <i id="file-icon" class="fas fa-file text-secondary me-2"></i>
                                    <div class="flex-grow-1">
                                        <div id="file-name-display" class="font-weight-bold small">-</div>
                                        <small id="file-size-display" class="text-muted">-</small>
                                    </div>
                                    <div class="btn-group" role="group">
                                        <a id="file-view-btn" href="#" target="_blank" class="btn btn-sm btn-outline-primary" style="display: none;">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a id="file-download-btn" href="#" download class="btn btn-sm btn-outline-success" style="display: none;">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    </div>
                                </div>
                                <div id="file-type-display" class="text-muted small">-</div>
                            </div>
                            <div id="no-file-display" class="text-muted text-center py-2">
                                <i class="fas fa-file-slash"></i> Tidak ada file
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="keterangan" class="form-label">{{__('Keterangan')}}</label>
                            <textarea name="keterangan" class="form-control" rows="3" placeholder="Keterangan surat"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="notes" class="form-label">{{__("notes")}}</label>
                            <textarea name="notes" class="form-control" rows="4" placeholder="Catatan tambahan"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" id="kembali">{{__("cancel")}}</button>
                <button type="button" class="btn btn-success" id="simpan">{{__("save")}}</button>
            </div>
        </div>
    </div>
</div>

<style>
/* Pastikan modal tidak terlalu tinggi */
.modal-dialog-centered {
    max-height: 90vh;
}

.modal-content {
    max-height: 90vh;
    display: flex;
    flex-direction: column;
}

.modal-body {
    flex: 1;
    overflow-y: auto;
    max-height: 70vh;
}

/* Kompak file info display */
#file-info-display .small {
    font-size: 0.85em;
}

/* Responsive untuk layar kecil */
@media (max-height: 768px) {
    .modal-body {
        max-height: 60vh;
    }
}

@media (max-height: 600px) {
    .modal-body {
        max-height: 50vh;
    }
}
</style>

                <div class="card-body">
                    <div class="table-responsive">
                        <table id="data-tabel" width="100%" class="table table-bordered text-nowrap border-bottom">
                            <thead>
                                <tr>
                                    <th class="border-bottom-0" width="5%">{{__("no")}}</th>
                                    <th class="border-bottom-0">{{__("Tanggal")}}</th>
                                    <th class="border-bottom-0">{{__("Nomor Surat")}}</th>
                                    <th class="border-bottom-0">{{__("Perihal")}}</th>
                                    <th class="border-bottom-0">{{__("Tujuan")}}</th> <!-- KOLOM BARU -->
                                    <th class="border-bottom-0">{{__("Keterangan")}}</th>
                                    <th class="border-bottom-0" width="20%">{{__("Detail File Surat")}}</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<x-data-table/>

<script>
    // Global variables
    let letterTable;
    let mainTable;

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    function getFileIcon(fileType) {
        const icons = {
            'application/pdf': 'fas fa-file-pdf text-danger',
            'application/msword': 'fas fa-file-word text-primary',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document': 'fas fa-file-word text-primary',
            'image/jpeg': 'fas fa-file-image text-success',
            'image/jpg': 'fas fa-file-image text-success',
            'image/png': 'fas fa-file-image text-success'
        };
        return icons[fileType] || 'fas fa-file text-secondary';
    }

    function updateFileDisplay(fileData) {
        if (fileData && fileData.file_name) {
            // Show file info
            $('#file-info-display').show();
            $('#no-file-display').hide();
            
            // Update file details
            $('#file-icon').attr('class', getFileIcon(fileData.file_type));
            $('#file-name-display').text(fileData.file_name);
            $('#file-size-display').text(fileData.file_size ? formatFileSize(fileData.file_size) : 'Unknown size');
            $('#file-type-display').text(fileData.file_type ? fileData.file_type : 'Unknown type');
            
            // Update buttons
            if (fileData.file_path) {
                const fileUrl = '{{ asset("storage") }}/' + fileData.file_path;
                $('#file-view-btn').attr('href', fileUrl).show();
                $('#file-download-btn').attr('href', fileUrl).attr('download', fileData.file_name).show();
            } else {
                $('#file-view-btn').hide();
                $('#file-download-btn').hide();
            }
        } else {
            // Hide file info
            $('#file-info-display').hide();
            $('#no-file-display').show();
        }
    }

    function initializeLetterTable() {
        if (letterTable) {
            letterTable.destroy();
        }
        
        letterTable = $('#data-letter').DataTable({
            lengthChange: true,
            processing: true,
            serverSide: true,
            destroy: true,
            responsive: true,
            ajax: {
                url: "{{route('surat.keluar.list.letters')}}",
                type: 'POST',
                error: function(xhr, error, code) {
                    console.log('Ajax error:', xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error loading letters data'
                    });
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
                    data: 'code',
                    name: 'code',
                    defaultContent: '-'
                },
                {
                    data: 'name',
                    name: 'name',
                    defaultContent: '-'
                },
                {
                    data: 'sender_name',
                    name: 'sender_name',
                    defaultContent: '-'
                },
                {
                    data: 'from_department_display',
                    name: 'from_department_display',
                    defaultContent: '-'
                },
                {
                    data: 'category_name',
                    name: 'category_name',
                    defaultContent: '-'
                },
                {
                    data: 'file_status',
                    name: 'file_status',
                    defaultContent: '-'
                },
                {
                    data: 'file_info',
                    name: 'file_info',
                    defaultContent: '-'
                },
                {
                    data: 'tindakan',
                    name: 'tindakan',
                    orderable: false,
                    searchable: false
                }
            ],
            language: {
                processing: "Loading...",
                emptyTable: "No data available",
                zeroRecords: "No matching records found"
            }
        });

        // Custom search untuk letter table
        $('#search-letter').on('keyup', function() {
            letterTable.search(this.value).draw();
        });
    }

    function detail() {
        const letter_code = $("input[name='letter_code']").val().trim();
        if(!letter_code) {
            Swal.fire({
                icon: 'warning',
                title: 'Peringatan',
                text: 'Masukkan kode surat terlebih dahulu!'
            });
            return;
        }
        
        $.ajax({
            url: "{{route('surat.keluar.letter.code')}}",
            type: 'POST',
            data: {
                code: letter_code,
                "_token": "{{csrf_token()}}"
            },
            beforeSend: function() {
                $('#cari-letter').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
            },
            success: function(response) {
                if(response.success && response.data) {
                    const data = response.data;
                    $("input[name='letter_id']").val(data.id);
                    $("input[name='letter_name']").val(data.name);
                    
                    // Update file information
                    $("input[name='file_name']").val(data.file_name || '');
                    $("input[name='file_path']").val(data.file_path || '');
                    $("input[name='file_size']").val(data.file_size || '');
                    $("input[name='file_type']").val(data.file_type || '');
                    
                    updateFileDisplay(data);
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Surat ditemukan!',
                        timer: 1500,
                        showConfirmButton: false
                    });
                } else {
                    resetLetterFields();
                    updateFileDisplay(null);
                    Swal.fire({
                        icon: 'error',
                        title: 'Tidak Ditemukan',
                        text: 'Surat dengan kode tersebut tidak ditemukan!'
                    });
                }
            },
            error: function(xhr) {
                console.log('Error:', xhr.responseText);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error saat mencari surat!'
                });
            },
            complete: function() {
                $('#cari-letter').prop('disabled', false).html('<i class="fas fa-search"></i>');
            }
        });
    }

    function resetLetterFields() {
        const fields = ['letter_id', 'letter_name', 'file_name', 'file_path', 'file_size', 'file_type'];
        fields.forEach(field => {
            $(`[name='${field}']`).val('');
        });
    }

    function simpan() {
        const formData = {
            letter_id: $("input[name='letter_id']").val(),
            sent_date: $("input[name='sent_date']").val(),
            perihal: $("input[name='perihal']").val(),
            tujuan: $("input[name='tujuan']").val(),    // FIELD BARU
            keterangan: $("textarea[name='keterangan']").val(),
            notes: $("textarea[name='notes']").val(),
            file_name: $("input[name='file_name']").val(),
            file_path: $("input[name='file_path']").val(),
            file_size: $("input[name='file_size']").val(),
            file_type: $("input[name='file_type']").val(),
            _token: '{{csrf_token()}}'
        };

        // Validasi - UPDATE: Tambahkan tujuan
        const requiredFields = ['letter_id', 'sent_date', 'perihal', 'tujuan'];
        const missingFields = requiredFields.filter(field => !formData[field]);
        
        if (missingFields.length > 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Field Kosong',
                text: 'Mohon lengkapi semua field yang required!'
            });
            return;
        }

        $.ajax({
            url: "{{route('surat.keluar.store')}}",
            type: "POST",
            data: formData,
            beforeSend: function() {
                $('#simpan').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
            },
            success: function(res) {
                if(res.success) {
                    Swal.fire({
                        position: "center",
                        icon: "success",
                        title: res.message,
                        showConfirmButton: false,
                        timer: 1500
                    });
                    $('#kembali').click();
                    resetForm();
                    mainTable.ajax.reload();
                }
            },
            error: function(xhr) {
                console.log('Error:', xhr.responseText);
                const response = xhr.responseJSON;
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response?.message || 'Terjadi kesalahan saat menyimpan data!'
                });
            },
            complete: function() {
                $('#simpan').prop('disabled', false).html("{{__('save')}}");
            }
        });
    }

    function ubah() {
        const formData = {
            id: $("input[name='id']").val(),
            letter_id: $("input[name='letter_id']").val(),
            sent_date: $("input[name='sent_date']").val(),
            perihal: $("input[name='perihal']").val(),
            tujuan: $("input[name='tujuan']").val(),    // FIELD BARU
            keterangan: $("textarea[name='keterangan']").val(),
            notes: $("textarea[name='notes']").val(),
            file_name: $("input[name='file_name']").val(),
            file_path: $("input[name='file_path']").val(),
            file_size: $("input[name='file_size']").val(),
            file_type: $("input[name='file_type']").val(),
            "_token": "{{csrf_token()}}"
        };

        // Validasi - UPDATE: Tambahkan tujuan
        const requiredFields = ['id', 'letter_id', 'sent_date', 'perihal', 'tujuan'];
        const missingFields = requiredFields.filter(field => !formData[field]);
        
        if (missingFields.length > 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Field Kosong',
                text: 'Mohon lengkapi semua field yang required!'
            });
            return;
        }

        $.ajax({
            url: "{{route('surat.keluar.update')}}",
            type: "PUT",
            data: formData,
            beforeSend: function() {
                $('#simpan').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating...');
            },
            success: function(res) {
                if(res.success) {
                    Swal.fire({
                        position: "center",
                        icon: "success",
                        title: res.message,
                        showConfirmButton: false,
                        timer: 1500
                    });
                    $('#kembali').click();
                    resetForm();
                    mainTable.ajax.reload();
                }
            },
            error: function(xhr) {
                console.log('Error:', xhr.responseText);
                const response = xhr.responseJSON;
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: response?.message || 'Terjadi kesalahan saat mengubah data!'
                });
            },
            complete: function() {
                $('#simpan').prop('disabled', false).html("{{__('update')}}");
            }
        });
    }

    function resetForm() {
        // UPDATE: Tambahkan tujuan dalam reset
        const fields = ['id', 'letter_id', 'sent_date', 'letter_code', 'letter_name', 'perihal', 'tujuan', 'keterangan', 'notes', 'file_name', 'file_path', 'file_size', 'file_type'];
        fields.forEach(field => {
            $(`[name='${field}']`).val('');
        });
        updateFileDisplay(null);
    }

    $(document).ready(function() {
        // Initialize main DataTable
        mainTable = $('#data-tabel').DataTable({
            lengthChange: true,
            processing: true,
            serverSide: true,
            destroy: true,
            responsive: true,
            ajax: {
                url: "{{route('surat.keluar.list')}}",
                type: 'POST',
                error: function(xhr, error, code) {
                    console.log('Ajax error:', xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error loading data'
                    });
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
                { data: "sent_date_formatted", name: "sent_date_formatted", defaultContent: '-' },
                { data: "letter_code", name: "letter_code", defaultContent: '-' },
                { data: "perihal", name: "perihal", defaultContent: '-' },
                { data: "tujuan", name: "tujuan", defaultContent: '-' }, // KOLOM BARU
                { data: "keterangan", name: "keterangan", defaultContent: '-' },
                { 
                    data: "detail_file", 
                    name: "detail_file", 
                    orderable: false, 
                    searchable: false,
                    render: function(data, type, row) {
                        return '<div class="detail-file-wrapper">' + data + '</div>';
                    }
                }
            ],
            language: {
                processing: "Loading...",
                emptyTable: "No data available",
                zeroRecords: "No matching records found"
            }
        });

        // Event handlers
        $("#pilih-letter").on("click", function() {
            initializeLetterTable();
            $('#modal-letter').modal('show');
            $('#TambahData').modal('hide');
        });

        $("#close-modal-letter").on("click", function() {
            $('#modal-letter').modal('hide');
            $('#TambahData').modal('show');
        });

        $("#cari-letter").on("click", detail);

        $('#simpan').on('click', function() {
            if($(this).text().trim().includes("{{__('update')}}")) {
                ubah();
            } else {
                simpan();
            }
        });

        $("#modal-button").on("click", function() {
            resetForm();
            $('#simpan').text("{{__('save')}}");
            $('.modal-title').text("{{__('membuat surat keluar')}}");
        });

        // Pilih surat dari modal
        $(document).on("click", ".pilih-letter", function() {
            const data = {
                id: $(this).data("id"),
                code: $(this).data("code"),
                name: $(this).data("name"),
                file_name: $(this).data("file-name"),
                file_path: $(this).data("file-path"),
                file_size: $(this).data("file-size"),
                file_type: $(this).data("file-type")
            };
            
            // Populate form fields
            $("input[name='letter_code']").val(data.code);
            $("input[name='letter_id']").val(data.id);
            $("input[name='letter_name']").val(data.name);
            
            // Populate file fields
            $("input[name='file_name']").val(data.file_name || '');
            $("input[name='file_path']").val(data.file_path || '');
            $("input[name='file_size']").val(data.file_size || '');
            $("input[name='file_type']").val(data.file_type || '');
            
            updateFileDisplay(data);
            
            $('#modal-letter').modal('hide');
            $('#TambahData').modal('show');
        });

        // Edit data
        $(document).on("click", ".ubah", function() {
            $("#modal-button").click();
            $("#simpan").text("{{__('update')}}");
            $('.modal-title').text("{{__('edit outgoing letter')}}");
            
            let id = $(this).attr('id');
            $.ajax({
                url: "{{route('surat.keluar.show')}}",
                type: "POST",
                data: {
                    id: id,
                    "_token": "{{csrf_token()}}"
                },
                beforeSend: function() {
                    $(`.ubah[id="${id}"]`).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Loading...');
                },
                success: function(response) {
                    if(response.success && response.data) {
                        const data = response.data;
                        Object.keys(data).forEach(key => {
                            if (key !== 'has_file') {
                                $(`[name='${key}']`).val(data[key]);
                            }
                        });
                        updateFileDisplay(data);
                    }
                },
                error: function(xhr) {
                    console.log('Error:', xhr.responseText);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal mengambil data untuk edit!'
                    });
                },
                complete: function() {
                    $(`.ubah[id="${id}"]`).prop('disabled', false).html('<i class="fas fa-edit"></i> Edit');
                }
            });
        });

        // Delete data
        $(document).on("click", ".hapus", function() {
            let id = $(this).attr('id');
            Swal.fire({
                title: "{{__('you are sure')}} ?",
                text: "{{__('this data will be deleted')}}",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "{{__('yes, delete')}}",
                cancelButtonText: "{{__('no, cancel')}}"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{route('surat.keluar.destroy')}}",
                        type: "DELETE",
                        data: {
                            id: id,
                            "_token": "{{csrf_token()}}"
                        },
                        beforeSend: function() {
                            $(`.hapus[id="${id}"]`).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Deleting...');
                        },
                        success: function(res) {
                            if(res.success) {
                                Swal.fire({
                                    position: "center",
                                    icon: "success",
                                    title: res.message,
                                    showConfirmButton: false,
                                    timer: 1500
                                });
                                mainTable.ajax.reload();
                            }
                        },
                        error: function(xhr) {
                            console.log('Error:', xhr.responseText);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Gagal menghapus data!'
                            });
                            $(`.hapus[id="${id}"]`).prop('disabled', false).html('<i class="fas fa-trash"></i> Hapus');
                        }
                    });
                }
            });
        });
    });
</script>

<style>
.file-info-container {
    min-width: 200px;
}

.file-details {
    background-color: #f8f9fa;
    padding: 8px;
    border-radius: 4px;
    border-left: 3px solid #007bff;
}

.file-name {
    font-size: 0.9em;
    word-break: break-word;
}

.file-meta div {
    margin-bottom: 2px;
}

.badge {
    font-size: 0.8em;
}

.table td {
    vertical-align: middle;
}

.btn-group-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 2px;
}

.btn-sm {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}

.detail-file-wrapper {
    min-width: 250px;
}

.file-info {
    max-width: 200px;
}

.file-info-small {
    max-width: 150px;
}

.file-info div, .file-info-small div {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Responsive untuk mobile */
@media (max-width: 768px) {
    .file-info-container {
        min-width: 150px;
    }
    
    .file-details {
        padding: 6px;
    }
    
    .btn-group-actions {
        flex-direction: column;
    }
    
    .btn-sm {
        margin-bottom: 2px !important;
        margin-right: 0 !important;
        width: 100%;
    }
    
    .detail-file-wrapper {
        min-width: 200px;
    }
}

/* Styling untuk action buttons */
.btn-group-actions .btn {
    margin-right: 4px;
    margin-bottom: 4px;
}

.btn-group-actions .btn:last-child {
    margin-right: 0;
}

/* Improvement untuk readability */
.file-details .file-name {
    color: #333;
    font-weight: 600;
}

.file-meta small {
    color: #6c757d;
    font-size: 0.8em;
}

/* Hover effects */
.btn-group-actions .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
</style>
@endsection