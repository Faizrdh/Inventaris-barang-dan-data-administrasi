@extends('layouts.app')
@section('title', __("Satuan Surat"))

@section('content')
<x-head-datatable/>

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
        }
    }
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card w-100">
                <div class="card-header row">
                    <div class="d-flex justify-content-between align-items-center w-100">
                        <h5 class="card-title mb-0">{{ __("letter management") }}</h5>
                        @if(Auth::user()->role->name != 'staff')
                            <button class="btn btn-success" type="button" data-toggle="modal" data-target="#TambahData" id="modal-button">
                                <i class="fas fa-plus"></i> {{ __("add data") }}
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Modal -->
                <div class="modal fade" id="TambahData" tabindex="-1" aria-labelledby="TambahDataModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="TambahDataModalLabel">{{ __("Tambah data") }}</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <form id="letterForm" enctype="multipart/form-data">
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label for="code">{{ __("Nomor surat") }} <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="code" name="code" autocomplete="off" placeholder="Masukkan Nomor Surat">
                                                <input type="hidden" name="id" id="id">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label for="category_letter_id">{{ __("Jenis Surat") }} <span class="text-danger">*</span></label>
                                                <select class="form-control" id="category_letter_id" name="category_letter_id">
                                                    <option value="">{{ __("Pilih Jenis Surat") }}</option>
                                                    @foreach($jenissurat as $jenis)
                                                        <option value="{{ $jenis->id }}">{{ $jenis->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group mb-3">
                                        <label for="name">{{ __("Nama Surat") }} <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="name" name="name" autocomplete="off" placeholder="Masukkan nama surat">
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label for="sender_letter_id">{{ __("Pengirim surat") }}</label>
                                                <select class="form-control" id="sender_letter_id" name="sender_letter_id">
                                                    <option value="">{{ __("select Pengirim surat") }}</option>
                                                    @foreach($senderletters as $sender)
                                                        <option value="{{ $sender->id }}" data-department="{{ $sender->from_department }}">
                                                            {{ $sender->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label for="from_department">{{ __("from department") }}</label>
                                                <input type="text" class="form-control" id="from_department" name="Dari" autocomplete="off" placeholder="asal surat" readonly>
                                                <small class="form-text text-muted">Akan otomatis terisi jika memilih Pengirim surat</small>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group mb-3">
                                        <label for="file">{{ __("upload file") }} 
                                            <small class="text-muted">(PDF, DOC, DOCX, JPG, PNG - Max: 10MB)</small>
                                        </label>
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" id="file" name="file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                            <label class="custom-file-label" for="file">Pilih file...</label>
                                        </div>
                                        <small class="form-text text-muted">{{ __("Masukan surat dengan tipe file yang sesuai") }}</small>
                                    </div>
                                    
                                    <div id="current-file" class="form-group mb-3" style="display: none;">
                                        <label>{{ __("current file") }}</label>
                                        <div class="border p-3 rounded bg-light">
                                            <div id="file-info" class="mb-2"></div>
                                            <div class="d-flex gap-2">
                                                <button type="button" class="btn btn-outline-primary btn-sm" id="preview-file">
                                                    <i class="fas fa-eye"></i> {{ __("preview") }}
                                                </button>
                                                <button type="button" class="btn btn-outline-info btn-sm" id="download-current-file">
                                                    <i class="fas fa-download"></i> {{ __("download") }}
                                                </button>
                                                <button type="button" class="btn btn-outline-danger btn-sm" id="delete-file">
                                                    <i class="fas fa-trash"></i> {{ __("delete file") }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal" id="kembali">
                                        <i class="fas fa-times"></i> {{ __("cancel") }}
                                    </button>
                                    <button type="button" class="btn btn-success" id="simpan">
                                        <i class="fas fa-save"></i> <span id="save-text">{{ __("save") }}</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table id="data-surat" width="100%" class="table table-bordered text-nowrap border-bottom dataTable no-footer dtr-inline collapsed">
                            <thead class="thead-light">
                                <tr>
                                    <th class="border-bottom-0" width="5%">{{ __("no") }}</th>
                                    <th class="border-bottom-0">{{ __("Nomor Surat") }}</th>
                                    <th class="border-bottom-0">{{ __("Nama Surat") }}</th>
                                    <th class="border-bottom-0">{{ __("Jenis Surat") }}</th>
                                    <th class="border-bottom-0">{{ __("Nama Pengirim") }}</th>
                                    <th class="border-bottom-0">{{ __("Dari") }}</th>
                                    <th class="border-bottom-0" width="20%">{{ __("detail surat") }}</th>
                                    @if(Auth::user()->role->name != 'staff')
                                    <th class="border-bottom-0" width="15%">{{ __("action") }}</th>
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
    let currentLetterId = null;
    let currentFileData = null;

    function isi(){
        $('#data-surat').DataTable({
            responsive: true, 
            lengthChange: true, 
            autoWidth: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: `{{route('surat.list')}}`,
                type: 'GET',
                error: function(xhr, error, thrown) {
                    console.log('DataTables Ajax Error:', xhr, error, thrown);
                }
            },
            columns: [
                {
                    "data": null, 
                    "sortable": false,
                    render: function(data, type, row, meta){
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    data: 'code',
                    name: 'code'
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'category_letter_name',
                    name: 'category_letter_name'
                },
                {
                    data: 'sender_name',
                    name: 'sender_name'
                },
                {
                    data: 'from_department',
                    name: 'from_department'
                },
                {
                    data: 'file_info',
                    name: 'file_info',
                    orderable: false,
                    searchable: false
                },
                @if(Auth::user()->role->name != 'staff')
                {
                    data: 'tindakan',
                    name: 'tindakan',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        return '<div class="btn-group-actions">' + data + '</div>';
                    }
                }
                @endif
            ],
            language: {
                processing: "Memuat data...",
                lengthMenu: "Tampilkan _MENU_ data per halaman",
                zeroRecords: "Data tidak ditemukan",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                infoFiltered: "(disaring dari _MAX_ total data)",
                search: "Cari:",
                paginate: {
                    first: "Pertama",
                    last: "Terakhir",
                    next: "Selanjutnya",
                    previous: "Sebelumnya"
                }
            },
            drawCallback: function(settings) {
                console.log('DataTables draw callback - Data loaded:', settings.json);
            }
        });
    }

    function validateForm() {
        if($('#code').val().length == 0){
            showAlert('warning', 'Kode surat tidak boleh kosong!');
            return false;
        }
        
        if($('#name').val().length == 0){
            showAlert('warning', 'Nama surat tidak boleh kosong!');
            return false;
        }
        
        if($('#category_letter_id').val().length == 0){
            showAlert('warning', 'Jenis surat tidak boleh kosong!');
            return false;
        }

        return true;
    }

    function showAlert(type, message) {
        Swal.fire({
            position: "center",
            icon: type,
            title: message,
            showConfirmButton: false,
            timer: 1500
        });
    }

    function simpan(){
        if (!validateForm()) return;

        const formData = new FormData();
        formData.append('code', $('#code').val());
        formData.append('name', $('#name').val());
        formData.append('category_letter_id', $('#category_letter_id').val());
        formData.append('sender_letter_id', $('#sender_letter_id').val());
        formData.append('from_department', $('#from_department').val());
        formData.append('_token', '{{csrf_token()}}');
        
        const fileInput = document.getElementById('file');
        if (fileInput.files.length > 0) {
            formData.append('file', fileInput.files[0]);
            console.log('File akan diupload:', fileInput.files[0].name);
        }

        // Debug: Tampilkan semua data yang akan dikirim
        for (let pair of formData.entries()) {
            console.log(pair[0] + ': ' + pair[1]);
        }

        $.ajax({
            url: `{{route('surat.save')}}`,
            type: "post",
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function() {
                $('#simpan').prop('disabled', true);
                $('#save-text').text('{{ __("saving...") }}');
            },
            success: function(res){
                console.log('Response save:', res);
                showAlert('success', res.message);
                $('#kembali').click();
                clearForm();
                $('#data-surat').DataTable().ajax.reload();
            },
            error: function(err){
                console.log('Error save:', err);
                let message = "Terjadi kesalahan!";
                if (err.responseJSON && err.responseJSON.message) {
                    message = err.responseJSON.message;
                }
                showAlert('error', message);
            },
            complete: function() {
                $('#simpan').prop('disabled', false);
                $('#save-text').text('{{ __("save") }}');
            }
        });
    }

    function ubah(){
        if (!validateForm()) return;

        const formData = new FormData();
        formData.append('id', $('#id').val());
        formData.append('code', $('#code').val());
        formData.append('name', $('#name').val());
        formData.append('category_letter_id', $('#category_letter_id').val());
        formData.append('sender_letter_id', $('#sender_letter_id').val());
        formData.append('from_department', $('#from_department').val());
        formData.append('_token', '{{csrf_token()}}');
        formData.append('_method', 'PUT');
        
        const fileInput = document.getElementById('file');
        if (fileInput.files.length > 0) {
            formData.append('file', fileInput.files[0]);
            console.log('File akan diupdate:', fileInput.files[0].name);
        }

        // Debug: Tampilkan semua data yang akan dikirim
        for (let pair of formData.entries()) {
            console.log(pair[0] + ': ' + pair[1]);
        }

        $.ajax({
            url: `{{route('surat.update')}}`,
            type: "post",
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function() {
                $('#simpan').prop('disabled', true);
                $('#save-text').text('{{ __("updating...") }}');
            },
            success: function(res){
                console.log('Response update:', res);
                showAlert('success', res.message);
                $('#kembali').click();
                clearForm();
                $('#data-surat').DataTable().ajax.reload();
            },
            error: function(err){
                console.log('Error update:', err);
                let message = "Terjadi kesalahan!";
                if (err.responseJSON && err.responseJSON.message) {
                    message = err.responseJSON.message;
                }
                showAlert('error', message);
            },
            complete: function() {
                $('#simpan').prop('disabled', false);
                $('#save-text').text('{{ __("save changes") }}');
            }
        });
    }

    function clearForm(){
        $("#code").val('');
        $("#name").val('');
        $("#category_letter_id").val('');
        $("#sender_letter_id").val('');
        $("#from_department").val('');
        $("#id").val('');
        $("#file").val('');
        $(".custom-file-label").text('Pilih file...');
        $("#current-file").hide();
        currentLetterId = null;
        currentFileData = null;
    }

    function showCurrentFile(data) {
        currentFileData = data;
        if (data.file_name) {
            const fileSize = data.file_size ? formatFileSize(data.file_size) : 'Unknown size';
            const fileType = data.file_type || 'Unknown type';
            const uploadDate = data.updated_at ? new Date(data.updated_at).toLocaleDateString('id-ID') : '-';
            
            $("#file-info").html(`
                <div class="d-flex align-items-center mb-2">
                    <i class="${getFileIcon(fileType)} me-2" style="font-size: 1.5em;"></i>
                    <div>
                        <div class="fw-bold">${data.file_name}</div>
                        <small class="text-muted">
                            ${fileSize} • ${fileType.split('/').pop().toUpperCase()} • Upload: ${uploadDate}
                        </small>
                    </div>
                </div>
            `);
            $("#current-file").show();
        } else {
            $("#current-file").hide();
        }
    }

    function getFileIcon(mimeType) {
        const icons = {
            'application/pdf': 'fas fa-file-pdf text-danger',
            'application/msword': 'fas fa-file-word text-primary',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document': 'fas fa-file-word text-primary',
            'image/jpeg': 'fas fa-file-image text-success',
            'image/jpg': 'fas fa-file-image text-success',
            'image/png': 'fas fa-file-image text-success',
        };
        return icons[mimeType] || 'fas fa-file text-secondary';
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    $(document).ready(function(){
        isi();

        // Auto-fill from_department when sender is selected
        $('#sender_letter_id').on('change', function() {
            const selectedOption = $(this).find('option:selected');
            const department = selectedOption.data('department');
            
            if (department) {
                $('#from_department').val(department);
                $('#from_department').prop('readonly', true);
            } else {
                $('#from_department').val('');
                $('#from_department').prop('readonly', false);
            }
        });

        // Update file label when file is selected
        $('#file').on('change', function() {
            const filename = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').text(filename || 'Pilih file...');
        });

        $('#simpan').on('click', function(){
            if($('#save-text').text() === '{{ __("save changes") }}'){
                ubah();
            } else {
                simpan();
            }
        });

        $("#modal-button").on("click", function(){
            clearForm();
            $("#save-text").text("{{ __('save') }}");
            $("#TambahDataModalLabel").text("{{ __('add letter') }}");
            $("#from_department").prop('readonly', false);
        });

        // Preview current file
        $("#preview-file").on("click", function(){
            if (currentLetterId) {
                Swal.fire({
                    title: 'Membuka file...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                const viewWindow = window.open(`{{route('surat.view-file')}}?id=${currentLetterId}`, '_blank');
                
                setTimeout(() => {
                    Swal.close();
                }, 1000);
                
                if (!viewWindow) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Popup Diblokir',
                        text: 'Silakan izinkan popup untuk melihat file'
                    });
                }
            }
        });

        // Download current file
        $("#download-current-file").on("click", function(){
            if (currentLetterId) {
                Swal.fire({
                    title: 'Mengunduh file...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                const link = document.createElement('a');
                link.href = `{{route('surat.download-file')}}?id=${currentLetterId}`;
                link.download = '';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
                setTimeout(() => {
                    Swal.close();
                }, 1000);
            }
        });

        // Delete current file
        $("#delete-file").on("click", function(){
            if (!currentLetterId) return;

            Swal.fire({
                title: 'Hapus File?',
                text: "File yang dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{route('surat.delete-file')}}",
                        type: "delete",
                        data: {
                            id: currentLetterId,
                            "_token": "{{csrf_token()}}"
                        },
                        success: function(res){
                            showAlert('success', res.message);
                            $("#current-file").hide();
                            $('#data-surat').DataTable().ajax.reload();
                        },
                        error: function(err){
                            console.log(err);
                            showAlert('error', 'Gagal menghapus file!');
                        }
                    });
                }
            });
        });
    });

    // Event handlers for table actions
    $(document).on("click", ".ubah", function(){
        let id = $(this).attr('id');
        currentLetterId = id;
        $("#modal-button").click();
        $("#save-text").text("{{ __('save changes') }}");
        $("#TambahDataModalLabel").text("{{ __('edit letter') }}");
        
        $.ajax({
            url: "{{route('surat.detail')}}",
            type: "post",
            data: {
                id: id,
                "_token": "{{csrf_token()}}"
            },
            success: function(response){
                console.log('Response detail:', response);
                const data = response.data;
                $("#id").val(data.id);
                $("#code").val(data.code);
                $("#name").val(data.name);
                $("#category_letter_id").val(data.category_letter_id);
                $("#sender_letter_id").val(data.sender_letter_id || '');
                $("#from_department").val(data.from_department || '');
                
                // Set readonly status berdasarkan apakah ada sender_letter_id
                if (data.sender_letter_id) {
                    $("#from_department").prop('readonly', true);
                } else {
                    $("#from_department").prop('readonly', false);
                }
                
                showCurrentFile(data);
            },
            error: function(err){
                console.log('Error detail:', err);
                showAlert('error', 'Gagal mengambil data!');
            }
        });
    });

    $(document).on("click", ".download", function(){
        let id = $(this).attr('id');
        // Tampilkan loading
        Swal.fire({
            title: 'Mengunduh file...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        // Buat link download
        const link = document.createElement('a');
        link.href = `{{route('surat.download-file')}}?id=${id}`;
        link.download = '';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        // Tutup loading setelah delay singkat
        setTimeout(() => {
            Swal.close();
        }, 1000);
    });

    $(document).on("click", ".view", function(){
        let id = $(this).attr('id');
        // Tampilkan loading
        Swal.fire({
            title: 'Membuka file...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        // Buka file di tab baru
        const viewWindow = window.open(`{{route('surat.view-file')}}?id=${id}`, '_blank');
        
        // Tutup loading setelah delay singkat
        setTimeout(() => {
            Swal.close();
        }, 1000);
        
        // Jika popup diblokir
        if (!viewWindow) {
            Swal.fire({
                icon: 'warning',
                title: 'Popup Diblokir',
                text: 'Silakan izinkan popup untuk melihat file',
                showConfirmButton: true
            });
        }
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
            title: "Anda Yakin?",
            text: "Data Ini Akan Di Hapus Beserta File Terlampir",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Ya, Hapus",
            cancelButtonText: "Tidak, Kembali!",
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{route('surat.delete')}}",
                    type: "delete",
                    data: {
                        id: id,
                        "_token": "{{csrf_token()}}"
                    },
                    beforeSend: function() {
                        Swal.fire({
                            title: 'Menghapus...',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                    },
                    success: function(res){
                        showAlert('success', res.message);
                        $('#data-surat').DataTable().ajax.reload();
                    },
                    error: function(err){
                        console.log(err);
                        showAlert('error', 'Gagal menghapus data!');
                    }
                });
            }
        });
    });
</script>
@endsection