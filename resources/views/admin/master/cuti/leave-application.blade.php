@extends('layouts.app')
@section('title', __('Pengajuan Cuti'))
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
                            <button class="btn btn-success" type="button" data-toggle="modal" data-target="#LeaveApplicationModal" id="modal-button">
                                <i class="fas fa-plus m-1"></i> {{__('tambah data')}}
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Leave Application Modal -->
                <div class="modal fade" id="LeaveApplicationModal" tabindex="-1" aria-labelledby="LeaveApplicationModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="LeaveApplicationModalLabel">{{__("Create Leave Application")}}</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="code" class="form-label">{{__("Kode cuti")}}<span class="text-danger">*</span></label>
                                            <input type="text" name="code" readonly class="form-control">
                                            <input type="hidden" name="id"/>
                                        </div>
                                        <div class="form-group">
                                            <label for="name" class="form-label">{{__("Nama Pegawai")}}<span class="text-danger">*</span></label>
                                            <input type="text" name="name" class="form-control">
                                        </div>
                                        <div class="form-group">
                                            <label for="email" class="form-label">{{__("Email")}}<span class="text-danger">*</span></label>
                                            <input type="email" name="email" class="form-control" placeholder="nama@email.com">
                                        </div>
                                        <div class="form-group">
                                            <label for="employee_id" class="form-label">{{__("NIP")}}<span class="text-danger">*</span></label>
                                            <input type="text" name="employee_id" class="form-control">
                                        </div>
                                        <div class="form-group">
                                            <label for="application_date" class="form-label">{{__("Tanggal Pengajuan")}} <span class="text-danger">*</span></label>
                                            <input type="date" name="application_date" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="leave_type" class="form-label">{{__("Jenis Cuti")}}<span class="text-danger">*</span></label>
                                            <select name="leave_type" class="form-control">
                                                <option selected value="">-- {{__("Silahkan pilih salah satu izin")}} --</option>
                                                <option value="Cuti Sakit">{{__("Cuti Sakit")}}</option>
                                                <option value="Cuti Melahirkan">{{__("Cuti Melahirkan")}}</option>
                                                <option value="Cuti Alasan Penting">{{__("Cuti Alasan Penting")}}</option>
                                                <option value="CLTN">{{__("CLTN")}}</option>
                                                <option value="Sakit">{{__("Sakit")}}</option>
                                                <option value="Dinas Luar">{{__("Dinas Luar")}}</option>
                                                <option value="Cuti Tahunan">{{__("Cuti Tahunan")}}</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="start_date" class="form-label">{{__("Tanggal Mulai")}} <span class="text-danger">*</span></label>
                                            <input type="date" name="start_date" class="form-control">
                                        </div>
                                        <div class="form-group">
                                            <label for="end_date" class="form-label">{{__("Tanggal Selesai")}} <span class="text-danger">*</span></label>
                                            <input type="date" name="end_date" class="form-control">
                                        </div>
                                        <div class="form-group">
                                            <label for="total_days" class="form-label">{{__("Total Hari")}}</label>
                                            <input type="number" name="total_days" readonly class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="description" class="form-label">{{__("Deskripsi")}}<span class="text-danger">*</span></label>
                                    <textarea name="description" class="form-control" rows="3"></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="document" class="form-label">{{__("Surat Izin")}} (PDF)</label>
                                    <input type="file" name="document" class="form-control-file" accept=".pdf">
                                    <small class="form-text text-muted">{{__("Maximum file size: 5MB")}}</small>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal" id="cancel">{{__("Cancel")}}</button>
                                <button type="button" class="btn btn-success" id="save">{{__("Save")}}</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table id="data-table" width="100%" class="table table-bordered text-nowrap border-bottom dataTable no-footer dtr-inline collapsed">
                            <thead>
                                <tr>
                                    <th class="border-bottom-0" width="5%">{{__("No")}}</th>
                                    <th class="border-bottom-0">{{__("Kode cuti")}}</th>
                                    <th class="border-bottom-0">{{__("Nama Pegawai")}}</th>
                                    <th class="border-bottom-0">{{__("NIP")}}</th>
                                    <th class="border-bottom-0">{{__("Email")}}</th>
                                    <th class="border-bottom-0">{{__("Tanggal Pengajuan")}}</th>
                                    <th class="border-bottom-0">{{__("Jenis Cuti")}}</th>
                                    <th class="border-bottom-0">{{__("Tanggal Mulai")}}</th>
                                    <th class="border-bottom-0">{{__("Tanggal Selesai")}}</th>
                                    <th class="border-bottom-0">{{__("Total Hari")}}</th>
                                    <th class="border-bottom-0">{{__("Status")}}</th>
                                    @if(Auth::user()->role->name == 'employee')
                                        <th class="border-bottom-0" width="1%">{{__("Actions")}}</th>
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
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Function to calculate total days
    function calculateTotalDays() {
        const startDate = $("input[name='start_date']").val();
        const endDate = $("input[name='end_date']").val();
        
        if (startDate && endDate) {
            const start = new Date(startDate);
            const end = new Date(endDate);
            
            if (end < start) {
                Swal.fire({
                    position: "center",
                    icon: "warning",
                    title: "{{__('Tanggal gagal di muat')}}",
                    text: "{{__('Tolong masukan tanggal sebelum mulai pengajuan')}}",
                    showConfirmButton: true
                });
                $("input[name='end_date']").val('');
                $("input[name='total_days']").val('');
                return;
            }
            
            const diffTime = Math.abs(end - start);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
            $("input[name='total_days']").val(diffDays);
        }
    }

    // Function to generate leave code
    function generateLeaveCode() {
        const currentDate = new Date();
        const year = currentDate.getFullYear().toString().substr(-2);
        const month = (currentDate.getMonth() + 1).toString().padStart(2, '0');
        const random = Math.floor(Math.random() * 9000 + 1000);
        const code = `CTI-${year}${month}-${random}`;
        $("input[name='code']").val(code);
    }

    // Function to validate email format
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    // Function to save leave application
    function saveLeaveApplication() {
        const formData = new FormData();
        
        formData.append('user_id', "{{ Auth::user()->id }}");
        formData.append('code', $("input[name='code']").val());
        formData.append('name', $("input[name='name']").val());
        formData.append('employee_id', $("input[name='employee_id']").val());
        formData.append('email', $("input[name='email']").val());
        formData.append('application_date', $("input[name='application_date']").val());
        formData.append('leave_type', $("select[name='leave_type']").val());
        formData.append('start_date', $("input[name='start_date']").val());
        formData.append('end_date', $("input[name='end_date']").val());
        formData.append('total_days', $("input[name='total_days']").val());
        formData.append('description', $("textarea[name='description']").val());
        formData.append('status', 'pending');

        if ($("input[name='document']")[0].files[0]) {
            formData.append('document', $("input[name='document']")[0].files[0]);
        }
        
        $.ajax({
            url: "{{route('leave-application.save')}}",
            type: "POST",
            processData: false,
            contentType: false,
            dataType: 'json',
            data: formData,
            success: function(res) {
                Swal.fire({
                    position: "center",
                    icon: "success",
                    title: res.message,
                    showConfirmButton: false,
                    timer: 1500
                });
                $('#cancel').click();
                resetForm();
                $('#data-table').DataTable().ajax.reload();
            },
            error: function(xhr) {
                let message = "{{__('An error occurred while saving data')}}";
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    let errors = xhr.responseJSON.errors;
                    let errorMessages = [];
                    for (let field in errors) {
                        errorMessages.push(errors[field][0]);
                    }
                    message = errorMessages.join('<br>');
                }
                Swal.fire({
                    position: "center",
                    icon: "error",
                    title: "{{__('Failed!')}}",
                    html: message,
                    showConfirmButton: true
                });
            }
        });
    }

    // Function to update leave application
    function updateLeaveApplication() {
        const formData = new FormData();
        
        formData.append('id', $("input[name='id']").val());
        formData.append('code', $("input[name='code']").val());
        formData.append('name', $("input[name='name']").val());
        formData.append('employee_id', $("input[name='employee_id']").val());
        formData.append('email', $("input[name='email']").val());
        formData.append('application_date', $("input[name='application_date']").val());
        formData.append('leave_type', $("select[name='leave_type']").val());
        formData.append('start_date', $("input[name='start_date']").val());
        formData.append('end_date', $("input[name='end_date']").val());
        formData.append('total_days', $("input[name='total_days']").val());
        formData.append('description', $("textarea[name='description']").val());

        if ($("input[name='document']")[0].files[0]) {
            formData.append('document', $("input[name='document']")[0].files[0]);
        }
        
        $.ajax({
            url: "{{route('leave-application.update')}}",
            type: "POST",
            processData: false,
            contentType: false,
            dataType: 'json',
            data: formData,
            success: function(res) {
                Swal.fire({
                    position: "center",
                    icon: "success",
                    title: res.message,
                    showConfirmButton: false,
                    timer: 1500
                });
                $('#cancel').click();
                resetForm();
                $('#data-table').DataTable().ajax.reload();
            },
            error: function(xhr) {
                let message = "{{__('An error occurred while updating data')}}";
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    let errors = xhr.responseJSON.errors;
                    let errorMessages = [];
                    for (let field in errors) {
                        errorMessages.push(errors[field][0]);
                    }
                    message = errorMessages.join('<br>');
                }
                Swal.fire({
                    position: "center",
                    icon: "error",
                    title: "{{__('Failed!')}}",
                    html: message,
                    showConfirmButton: true
                });
            }
        });
    }

    // Function to reset form
    function resetForm() {
        $("input[name='id']").val('');
        $("input[name='code']").val('');
        $("input[name='name']").val('');
        $("input[name='employee_id']").val('');
        $("input[name='email']").val('');
        $("input[name='application_date']").val('');
        $("select[name='leave_type']").val('');
        $("input[name='start_date']").val('');
        $("input[name='end_date']").val('');
        $("input[name='total_days']").val('');
        $("textarea[name='description']").val('');
        $("input[name='document']").val('');
    }

    $(document).ready(function() {
        // Initialize DataTable
        $('#data-table').DataTable({
            lengthChange: true,
            processing: true,
            serverSide: true,
            ajax: "{{route('leave-application.list')}}",
            columns: [
                {
                    "data": null, "sortable": false,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    data: "code",
                    name: "code"
                },
                {
                    data: "name",
                    name: "name"
                },
                {
                    data: "employee_id",
                    name: "employee_id"
                },
                {
                    data: "email",
                    name: "email"
                },
                {
                    data: "application_date",
                    name: "application_date"
                },
                {
                    data: "leave_type",
                    name: "leave_type"
                },
                {
                    data: "start_date",
                    name: "start_date"
                },
                {
                    data: "end_date",
                    name: "end_date"
                },
                {
                    data: "total_days",
                    name: "total_days"
                },
                {
                    data: "status",
                    name: "status",
                    render: function(data) {
                        let badgeClass = 'badge-warning';
                        let displayText = data;
                        if (data == 'approved') {
                            badgeClass = 'badge-success';
                            displayText = '{{__("Approved")}}';
                        } else if (data == 'rejected') {
                            badgeClass = 'badge-danger';
                            displayText = '{{__("Rejected")}}';
                        } else if (data == 'pending') {
                            displayText = '{{__("Pending")}}';
                        }
                        return `<span class="badge ${badgeClass}">${displayText}</span>`;
                    }
                },
                {{-- Kolom action hanya untuk employee --}}
                @if(Auth::user()->role->name == 'employee')
                {
                    data: "actions",
                    name: "actions",
                    orderable: false,
                    searchable: false
                }
                @endif
            ]
        });

        // Event handler for date inputs
        $("input[name='start_date'], input[name='end_date']").on('change', calculateTotalDays);

        // Event handler for add button
        $('#modal-button').on('click', function() {
            resetForm();
            generateLeaveCode();
            const today = new Date().toISOString().split('T')[0];
            $("input[name='application_date']").val(today);
            $('#LeaveApplicationModalLabel').text("{{__('Create Leave Application')}}");
            $('#save').text("{{__('Save')}}");
        });

        // Event handler for save button
        $('#save').on('click', function() {
            setTimeout(function() {
                let errors = [];
                
                if (!$("input[name='name']").val().trim()) errors.push('Nama Pegawai');
                if (!$("input[name='employee_id']").val().trim()) errors.push('NIP');
                
                const email = $("input[name='email']").val().trim();
                if (!email) {
                    errors.push('Email');
                } else if (!isValidEmail(email)) {
                    errors.push('Format Email tidak valid');
                }
                
                if (!$("input[name='application_date']").val()) errors.push('Tanggal Pengajuan');
                if (!$("select[name='leave_type']").val()) errors.push('Jenis Cuti');
                if (!$("input[name='start_date']").val()) errors.push('Tanggal Mulai');
                if (!$("input[name='end_date']").val()) errors.push('Tanggal Selesai');
                
                let desc = $("textarea[name='description']").val();
                if (!desc || desc.trim() === '') {
                    errors.push('Deskripsi');
                }
                
                if (errors.length > 0) {
                    Swal.fire({
                        position: "center",
                        icon: "warning", 
                        title: "Form Tidak Lengkap",
                        text: "Field yang belum diisi atau tidak valid: " + errors.join(', '),
                        showConfirmButton: true
                    });
                    return;
                }
                
                if ($("input[name='id']").val()) {
                    updateLeaveApplication();
                } else {
                    saveLeaveApplication();
                }
            }, 100);
        });

        // Event handler for edit button - hanya untuk employee
        @if(Auth::user()->role->name == 'employee')
        $(document).on("click", ".edit", function() {
            let id = $(this).data('id');
            $.ajax({
                url: "{{route('leave-application.detail')}}",
                type: "post",
                data: {
                    id: id,
                },
                success: function({data}) {
                    $('#LeaveApplicationModal').modal('show');
                    $('#LeaveApplicationModalLabel').text("{{__('Edit Leave Application')}}");
                    $('#save').text("{{__('Save Changes')}}");
                    
                    $("input[name='id']").val(data.id);
                    $("input[name='code']").val(data.code);
                    $("input[name='name']").val(data.name);
                    $("input[name='employee_id']").val(data.employee_id);
                    $("input[name='email']").val(data.email);
                    $("input[name='application_date']").val(data.application_date);
                    $("select[name='leave_type']").val(data.leave_type);
                    $("input[name='start_date']").val(data.start_date);
                    $("input[name='end_date']").val(data.end_date);
                    $("input[name='total_days']").val(data.total_days);
                    $("textarea[name='description']").val(data.description);
                },
                error: function(xhr) {
                    let message = "{{__('An error occurred while fetching data')}}";
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        position: "center",
                        icon: "error",
                        title: "{{__('Failed!')}}",
                        text: message,
                        showConfirmButton: true
                    });
                }
            });
        });

        // Event handler for delete button - hanya untuk employee
        $(document).on("click", ".delete", function() {
            let id = $(this).data('id');
            const swalWithBootstrapButtons = Swal.mixin({
                customClass: {
                    confirmButton: "btn btn-success m-1",
                    cancelButton: "btn btn-danger m-1"
                },
                buttonsStyling: false
            });
            swalWithBootstrapButtons.fire({
                title: "{{__('Are you sure?')}}",
                text: "{{__('This data will be deleted')}}",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "{{__('Yes, Delete')}}",
                cancelButtonText: "{{__('No, Cancel!')}}",
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{route('leave-application.delete')}}",
                        type: "POST",
                        data: {
                            id: id,
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(res) {
                            Swal.fire({
                                position: "center",
                                icon: "success",
                                title: res.message,
                                showConfirmButton: false,
                                timer: 1500
                            });
                            $('#data-table').DataTable().ajax.reload();
                        },
                        error: function(xhr) {
                            let message = "{{__('An error occurred while deleting data')}}";
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                message = xhr.responseJSON.message;
                            }
                            Swal.fire({
                                position: "center",
                                icon: "error",
                                title: "{{__('Failed!')}}",
                                text: message,
                                showConfirmButton: true
                            });
                        }
                    });
                }
            });
        });
        @endif

        // Event handler for detail button - semua role bisa akses
        $(document).on("click", ".detail", function() {
            let id = $(this).data('id');
            $.ajax({
                url: "{{route('leave-application.detail')}}",
                type: "post",
                data: {
                    id: id,
                },
                success: function({data}) {
                    let documentLink = '';
                    if (data.document_path) {
                        const documentUrl = "{{ asset('storage/') }}/" + data.document_path;
                        documentLink = `<p><strong>{{__('Document')}}:</strong> <a href="${documentUrl}" target="_blank">{{__('View Document')}}</a></p>`;
                    }
                    
                    Swal.fire({
                        title: '{{__("Leave Application Details")}}',
                        html: `
                            <div class="text-left">
                                <p><strong>{{__('Code')}}:</strong> ${data.code}</p>
                                <p><strong>{{__('Employee Name')}}:</strong> ${data.name}</p>
                                <p><strong>{{__('Employee ID')}}:</strong> ${data.employee_id}</p>
                                <p><strong>{{__('Email')}}:</strong> ${data.email}</p>
                                <p><strong>{{__('Application Date')}}:</strong> ${data.application_date}</p>
                                <p><strong>{{__('Leave Type')}}:</strong> ${data.leave_type}</p>
                                <p><strong>{{__('Start Date')}}:</strong> ${data.start_date}</p>
                                <p><strong>{{__('End Date')}}:</strong> ${data.end_date}</p>
                                <p><strong>{{__('Total Days')}}:</strong> ${data.total_days}</p>
                                <p><strong>{{__('Description')}}:</strong> ${data.description}</p>
                                <p><strong>{{__('Status')}}:</strong> ${data.status}</p>
                                ${documentLink}
                            </div>
                        `,
                        confirmButtonText: '{{__("Close")}}',
                        width: '600px'
                    });
                },
                error: function(xhr) {
                    let message = "{{__('An error occurred while fetching data')}}";
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        position: "center",
                        icon: "error",
                        title: "{{__('Failed!')}}",
                        text: message,
                        showConfirmButton: true
                    });
                }
            });
        });
    });
</script>
@endsection