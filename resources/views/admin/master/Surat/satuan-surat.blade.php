@extends('layouts.app')
@section('title', __("Satuan Surat"))

@section('content')
<x-head-datatable/>

<style>
    .file-info-container { min-width: 200px; }
    .file-details {
        background-color: #f8f9fa;
        padding: 8px;
        border-radius: 4px;
        border-left: 3px solid #007bff;
    }
    .file-name { font-size: 0.9em; word-break: break-word; }
    .file-meta div { margin-bottom: 2px; }
    .badge { font-size: 0.8em; }
    .table td { vertical-align: middle; }
    .btn-group-actions { display: flex; flex-wrap: wrap; gap: 2px; }
    .btn-sm { font-size: 0.75rem; padding: 0.25rem 0.5rem; }
    
    @media (max-width: 768px) {
        .file-info-container { min-width: 150px; }
        .file-details { padding: 6px; }
        .btn-group-actions { flex-direction: column; }
        .btn-sm { margin-bottom: 2px !important; margin-right: 0 !important; }
    }
</style>

<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card w-100">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center w-100">
                        <h5 class="card-title mb-0">{{ __("letter management") }}</h5>
                        @if(Auth::user()->role->name != 'staff')
                            <button class="btn btn-success" type="button" data-toggle="modal" data-target="#letterModal" id="modal-button">
                                <i class="fas fa-plus"></i> {{ __("add data") }}
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Letter Form Modal -->
                <div class="modal fade" id="letterModal" tabindex="-1" aria-labelledby="letterModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="letterModalLabel">{{ __("Tambah data") }}</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <form id="letterForm" enctype="multipart/form-data">
                                <div class="modal-body">
                                    <input type="hidden" name="id" id="id">
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label for="code">{{ __("Nomor surat") }} <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="code" name="code" autocomplete="off" placeholder="Masukkan Nomor Surat">
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
                                                <input type="text" class="form-control" id="from_department" name="from_department" autocomplete="off" placeholder="asal surat" readonly>
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
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                        <i class="fas fa-times"></i> {{ __("cancel") }}
                                    </button>
                                    <button type="button" class="btn btn-success" id="save-btn">
                                        <i class="fas fa-save"></i> <span id="save-text">{{ __("save") }}</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Delete Confirmation Modal -->
                <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="deleteConfirmModalLabel">
                                    <i class="fas fa-exclamation-triangle text-warning"></i> 
                                    Konfirmasi Hapus
                                </h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="text-center">
                                    <i class="fas fa-trash-alt fa-3x text-danger mb-3"></i>
                                    <h5>Apakah Anda yakin ingin menghapus data ini?</h5>
                                    <p class="text-muted">
                                        Data yang dihapus masih dapat dikembalikan melalui fitur restore.<br>
                                        <strong>File yang terlampir akan tetap tersimpan.</strong>
                                    </p>
                                    <div id="delete-item-info" class="alert alert-info">
                                        <!-- Info item yang akan dihapus -->
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                    <i class="fas fa-times"></i> Batal
                                </button>
                                <button type="button" class="btn btn-danger" id="confirm-delete-btn">
                                    <i class="fas fa-trash"></i> Ya, Hapus Data
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table id="data-surat" width="100%" class="table table-bordered text-nowrap border-bottom dataTable">
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
    // Global Variables
    let currentLetterId = null;
    let currentFileData = null;
    let isEditMode = false;

    // Initialize DataTable
    function initDataTable() {
        $('#data-surat').DataTable({
            responsive: true,
            lengthChange: true,
            autoWidth: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: `{{route('surat.list')}}`,
                type: 'GET',
                error: (xhr, error, thrown) => console.log('DataTables Ajax Error:', xhr, error, thrown)
            },
            columns: [
                { data: null, sortable: false, render: (data, type, row, meta) => meta.row + meta.settings._iDisplayStart + 1 },
                { data: 'code', name: 'code' },
                { data: 'name', name: 'name' },
                { data: 'category_letter_name', name: 'category_letter_name' },
                { data: 'sender_name', name: 'sender_name' },
                { data: 'from_department', name: 'from_department' },
                { data: 'file_info', name: 'file_info', orderable: false, searchable: false },
                @if(Auth::user()->role->name != 'staff')
                { 
                    data: 'tindakan', 
                    name: 'tindakan', 
                    orderable: false, 
                    searchable: false,
                    render: (data) => `<div class="btn-group-actions">${data}</div>`
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
                    first: "Pertama", last: "Terakhir", 
                    next: "Selanjutnya", previous: "Sebelumnya"
                }
            }
        });
    }

    // Form Validation
    function validateForm() {
        const fields = [
            { id: 'code', message: 'Kode surat tidak boleh kosong!' },
            { id: 'name', message: 'Nama surat tidak boleh kosong!' },
            { id: 'category_letter_id', message: 'Jenis surat tidak boleh kosong!' }
        ];

        for (const field of fields) {
            if (!$(`#${field.id}`).val().trim()) {
                showAlert('warning', field.message);
                return false;
            }
        }
        return true;
    }

    // Alert Helper
    function showAlert(type, message) {
        Swal.fire({
            position: "center",
            icon: type,
            title: message,
            showConfirmButton: false,
            timer: 1500
        });
    }

    // Form Data Preparation
    function prepareFormData() {
        const formData = new FormData();
        const fields = ['code', 'name', 'category_letter_id', 'sender_letter_id', 'from_department'];
        
        if (isEditMode) formData.append('id', $('#id').val());
        fields.forEach(field => formData.append(field, $(`#${field}`).val()));
        formData.append('_token', '{{csrf_token()}}');
        
        if (isEditMode) formData.append('_method', 'PUT');
        
        const fileInput = document.getElementById('file');
        if (fileInput.files.length > 0) {
            formData.append('file', fileInput.files[0]);
        }
        
        return formData;
    }

    // Save/Update Letter
    function saveOrUpdateLetter() {
        if (!validateForm()) return;

        const formData = prepareFormData();
        const url = isEditMode ? '{{route("surat.update")}}' : '{{route("surat.save")}}';
        const actionText = isEditMode ? 'updating...' : 'saving...';

        $.ajax({
            url,
            type: "post",
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: () => {
                $('#save-btn').prop('disabled', true);
                $('#save-text').text(actionText);
            },
            success: (res) => {
                showAlert('success', res.message);
                closeModal();
                $('#data-surat').DataTable().ajax.reload();
            },
            error: (err) => {
                const message = err.responseJSON?.message || "Terjadi kesalahan!";
                showAlert('error', message);
            },
            complete: () => {
                $('#save-btn').prop('disabled', false);
                $('#save-text').text(isEditMode ? '{{ __("save changes") }}' : '{{ __("save") }}');
            }
        });
    }

    // Clear Form
    function clearForm() {
        $('#letterForm')[0].reset();
        $('.custom-file-label').text('Pilih file...');
        $('#current-file').hide();
        currentLetterId = null;
        currentFileData = null;
        isEditMode = false;
    }

    // Close Modal
    function closeModal() {
        $('#letterModal').modal('hide');
        clearForm();
    }

    // Show Current File Info
    function showCurrentFile(data) {
        currentFileData = data;
        if (data.file_name) {
            const fileSize = data.file_size ? formatFileSize(data.file_size) : 'Unknown size';
            const fileType = data.file_type || 'Unknown type';
            const uploadDate = data.updated_at ? new Date(data.updated_at).toLocaleDateString('id-ID') : '-';
            
            $('#file-info').html(`
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
            $('#current-file').show();
        } else {
            $('#current-file').hide();
        }
    }

    // File Helper Functions
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

    // File Actions
    function handleFileAction(action, id) {
        const actions = {
            download: () => {
                const link = document.createElement('a');
                link.href = `{{route('surat.download-file')}}?id=${id}`;
                link.download = '';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            },
            view: () => {
                const viewWindow = window.open(`{{route('surat.view-file')}}?id=${id}`, '_blank');
                if (!viewWindow) {
                    showAlert('warning', 'Silakan izinkan popup untuk melihat file');
                }
            }
        };

        if (actions[action]) {
            Swal.fire({
                title: action === 'download' ? 'Mengunduh file...' : 'Membuka file...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });
            
            actions[action]();
            setTimeout(() => Swal.close(), 1000);
        }
    }

    // Delete Confirmation
    function showDeleteConfirmation(id, letterData) {
        $('#delete-item-info').html(`
            <strong>Nomor Surat:</strong> ${letterData.code || 'N/A'}<br>
            <strong>Nama Surat:</strong> ${letterData.name || 'N/A'}
        `);
        
        $('#confirm-delete-btn').off('click').on('click', () => deleteLetter(id));
        $('#deleteConfirmModal').modal('show');
    }

    function deleteLetter(id) {
        $('#deleteConfirmModal').modal('hide');
        
        Swal.fire({
            title: 'Menghapus data...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        $.ajax({
            url: "{{route('surat.delete')}}",
            type: "delete",
            data: { id, "_token": "{{csrf_token()}}" },
            success: (res) => {
                showAlert('success', res.message);
                $('#data-surat').DataTable().ajax.reload();
            },
            error: () => showAlert('error', 'Gagal menghapus data!')
        });
    }

    // Event Handlers
    $(document).ready(function() {
        initDataTable();

        // Auto-fill department from sender
        $('#sender_letter_id').on('change', function() {
            const department = $(this).find('option:selected').data('department');
            $('#from_department').val(department || '').prop('readonly', !!department);
        });

        // File input change
        $('#file').on('change', function() {
            const filename = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').text(filename || 'Pilih file...');
        });

        // Save button
        $('#save-btn').on('click', saveOrUpdateLetter);

        // Add new letter
        $('#modal-button').on('click', function() {
            clearForm();
            $('#letterModalLabel').text('{{ __("add letter") }}');
            $('#from_department').prop('readonly', false);
        });

        // Current file actions
        $('#preview-file').on('click', () => currentLetterId && handleFileAction('view', currentLetterId));
        $('#download-current-file').on('click', () => currentLetterId && handleFileAction('download', currentLetterId));
        
        $('#delete-file').on('click', function() {
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
                        data: { id: currentLetterId, "_token": "{{csrf_token()}}" },
                        success: (res) => {
                            showAlert('success', res.message);
                            $('#current-file').hide();
                            $('#data-surat').DataTable().ajax.reload();
                        },
                        error: () => showAlert('error', 'Gagal menghapus file!')
                    });
                }
            });
        });
    });

    // Table Action Handlers
    $(document).on("click", ".ubah", function() {
        const id = $(this).attr('id');
        currentLetterId = id;
        isEditMode = true;
        
        $('#modal-button').click();
        $('#letterModalLabel').text('{{ __("edit letter") }}');
        
        $.ajax({
            url: "{{route('surat.detail')}}",
            type: "post",
            data: { id, "_token": "{{csrf_token()}}" },
            success: (response) => {
                const data = response.data;
                $('#id').val(data.id);
                $('#code').val(data.code);
                $('#name').val(data.name);
                $('#category_letter_id').val(data.category_letter_id);
                $('#sender_letter_id').val(data.sender_letter_id || '');
                $('#from_department').val(data.from_department || '').prop('readonly', !!data.sender_letter_id);
                showCurrentFile(data);
            },
            error: () => showAlert('error', 'Gagal mengambil data!')
        });
    });

    $(document).on("click", ".download", function() {
        handleFileAction('download', $(this).attr('id'));
    });

    $(document).on("click", ".view", function() {
        handleFileAction('view', $(this).attr('id'));
    });

    $(document).on("click", ".hapus", function() {
        const id = $(this).attr('id');
        
        // Get letter data first
        $.ajax({
            url: "{{route('surat.detail')}}",
            type: "post",
            data: { id, "_token": "{{csrf_token()}}" },
            success: (response) => showDeleteConfirmation(id, response.data),
            error: () => showDeleteConfirmation(id, { code: 'N/A', name: 'N/A' })
        });
    });
</script>
@endsection