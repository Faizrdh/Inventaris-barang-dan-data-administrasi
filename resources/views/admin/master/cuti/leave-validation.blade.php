@extends('layouts.app')
@section('title', __('Leave Validation'))
@section('content')
<x-head-datatable/>
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card w-100">
                <div class="card-header row">
                    <div class="d-flex justify-content-between align-items-center w-100">
                        <h4 class="mb-0">{{__('Leave Application Validation')}}</h4>
                        <div class="d-flex align-items-center">
                            <span class="badge badge-info mr-2">{{__('Admin Only')}}</span>
                        </div>
                    </div>
                </div>

                <!-- Detail Modal -->
                <div class="modal fade" id="DetailModal" tabindex="-1" aria-labelledby="DetailModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="DetailModalLabel">{{__("Leave Application Details")}}</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body" id="detail-content">
                                <!-- Detail content will be loaded here -->
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{__("Close")}}</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status Change Confirmation Modal -->
                <div class="modal fade" id="StatusChangeModal" tabindex="-1" aria-labelledby="StatusChangeModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="StatusChangeModalLabel">{{__("Confirm Status Change")}}</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form id="status-change-form">
                                    <input type="hidden" name="leave_id" id="leave_id">
                                    <input type="hidden" name="new_status" id="new_status">
                                    
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i>
                                        <span id="status-change-message"></span>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="catatan_validator" class="form-label">
                                            {{__("Validator Notes")}}
                                            <span class="text-danger" id="required-indicator" style="display: none;">*</span>
                                        </label>
                                        <textarea name="catatan_validator" id="catatan_validator" class="form-control" rows="4" placeholder="{{__('Enter your notes here...')}}"></textarea>
                                        <small class="form-text text-muted" id="note-requirement">{{__("Optional notes for this action")}}</small>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{__("Cancel")}}</button>
                                <button type="button" class="btn btn-primary" id="confirm-status-change">{{__("Confirm Change")}}</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table id="validation-table" width="100%" class="table table-bordered text-nowrap border-bottom dataTable no-footer dtr-inline collapsed">
                            <thead>
                                <tr>
                                    <th class="border-bottom-0" width="3%">{{__("No")}}</th>
                                    <th class="border-bottom-0">{{__("Code")}}</th>
                                    <th class="border-bottom-0">{{__("Employee Name")}}</th>
                                    <th class="border-bottom-0">{{__("Employee ID")}}</th>
                                    <th class="border-bottom-0">{{__("Leave Type")}}</th>
                                    <th class="border-bottom-0">{{__("Application Date")}}</th>
                                    <th class="border-bottom-0">{{__("Start Date")}}</th>
                                    <th class="border-bottom-0">{{__("End Date")}}</th>
                                    <th class="border-bottom-0">{{__("Total Days")}}</th>
                                    <th class="border-bottom-0" width="12%">{{__("Status")}}</th>
                                    <th class="border-bottom-0">{{__("Approved By")}}</th>
                                    <th class="border-bottom-0">{{__("Approved At")}}</th>
                                    <th class="border-bottom-0" width="12%">{{__("Actions")}}</th>
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

<style>
.status-dropdown {
    border-radius: 6px;
    border: 2px solid;
    font-size: 0.875rem;
    font-weight: 500;
    transition: all 0.3s ease-in-out;
    cursor: pointer;
}

.colored-dropdown {
    background-color: #fff3cd !important;
    border-color: #ffeaa7 !important;
    color: #856404 !important;
}

.status-dropdown:focus {
    outline: 0;
    box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
    border-color: #ffc107;
}

.status-dropdown:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

/* Styling untuk option elements */
.status-dropdown option {
    padding: 8px 12px;
    font-weight: 500;
    border-radius: 4px;
    margin: 2px 0;
}

/* Pending - Kuning */
.status-dropdown option[value="pending"] {
    background-color: #fff3cd !important;
    color: #856404 !important;
    border-left: 4px solid #ffc107;
}

/* Approve - Hijau */
.status-dropdown option[value="approved"] {
    background-color: #d4edda !important;
    color: #155724 !important;
    border-left: 4px solid #28a745;
}

/* Reject - Merah */
.status-dropdown option[value="rejected"] {
    background-color: #f8d7da !important;
    color: #721c24 !important;
    border-left: 4px solid #dc3545;
}

/* Process - Biru */
.status-dropdown option[value="processed"] {
    background-color: #d1ecf1 !important;
    color: #0c5460 !important;
    border-left: 4px solid #17a2b8;
}

/* Enhanced badge styling */
.badge {
    font-size: 0.875rem;
    padding: 6px 12px;
    border-radius: 20px;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.badge i {
    font-size: 0.8rem;
}

/* Animation for badge */
.badge {
    animation: fadeInScale 0.3s ease-in-out;
}

@keyframes fadeInScale {
    from {
        opacity: 0;
        transform: scale(0.8);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

/* Animation untuk perubahan status */
.status-changing {
    animation: statusChange 0.3s ease-in-out;
}

@keyframes statusChange {
    0% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.05);
        box-shadow: 0 0 20px rgba(0,0,0,0.2);
    }
    100% {
        transform: scale(1);
    }
}

/* Pulse animation untuk pending status */
.colored-dropdown {
    animation: pendingPulse 2s infinite;
}

@keyframes pendingPulse {
    0% {
        box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.4);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(255, 193, 7, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(255, 193, 7, 0);
    }
}

/* Responsive dropdown */
@media (max-width: 768px) {
    .status-dropdown {
        width: 120px !important;
        font-size: 0.8rem;
    }
}

/* Tooltip style for dropdown */
.status-dropdown {
    position: relative;
}

.status-dropdown::after {
    content: 'Click to change status';
    position: absolute;
    bottom: -25px;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(0,0,0,0.8);
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.7rem;
    opacity: 0;
    transition: opacity 0.3s;
    pointer-events: none;
    white-space: nowrap;
    z-index: 1000;
}

.status-dropdown:hover::after {
    opacity: 1;
}
</style>

<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $(document).ready(function() {
        // Initialize DataTable
        let table = $('#validation-table').DataTable({
            lengthChange: true,
            processing: true,
            serverSide: true,
            ajax: "{{route('leave-validation.list')}}",
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
                    data: "leave_type",
                    name: "leave_type"
                },
                {
                    data: "application_date_formatted",
                    name: "application_date"
                },
                {
                    data: "start_date_formatted",
                    name: "start_date"
                },
                {
                    data: "end_date_formatted",
                    name: "end_date"
                },
                {
                    data: "total_days",
                    name: "total_days"
                },
                {
                    data: "status_badge",
                    name: "status",
                    orderable: false,
                    searchable: false
                },
                {
                    data: "approver_name",
                    name: "approver_name",
                    orderable: false,
                    searchable: false
                },
                {
                    data: "approved_at_formatted",
                    name: "approved_at"
                },
                {
                    data: "tindakan",
                    name: "tindakan",
                    orderable: false,
                    searchable: false
                }
            ],
            order: [[0, 'desc']],
            drawCallback: function() {
                // Re-bind event handlers after table redraw
                bindStatusDropdownEvents();
            }
        });

        // Function to bind status dropdown events
        function bindStatusDropdownEvents() {
            $('.status-dropdown').off('change').on('change', function() {
                const leaveId = $(this).data('id');
                const newStatus = $(this).val();
                const currentStatus = 'pending';
                const dropdown = $(this);
                
                // Update dropdown appearance based on selection
                updateDropdownStyle(dropdown, newStatus);
                
                if (newStatus !== 'pending') {
                    // Show confirmation modal
                    showStatusChangeModal(leaveId, newStatus);
                    
                    // Reset dropdown to pending with a slight delay for visual feedback
                    setTimeout(() => {
                        dropdown.val('pending');
                        updateDropdownStyle(dropdown, 'pending');
                    }, 200);
                }
            });

            // Add hover effects
            $('.status-dropdown').off('mouseenter mouseleave')
                .on('mouseenter', function() {
                    $(this).css('transform', 'translateY(-1px)');
                })
                .on('mouseleave', function() {
                    $(this).css('transform', 'translateY(0)');
                });
        }

        // Function to update dropdown styling based on selected value
        function updateDropdownStyle(dropdown, status) {
            const styles = {
                'pending': {
                    'background-color': '#fff3cd',
                    'border-color': '#ffeaa7',
                    'color': '#856404'
                },
                'approved': {
                    'background-color': '#d4edda',
                    'border-color': '#c3e6cb',
                    'color': '#155724'
                },
                'rejected': {
                    'background-color': '#f8d7da',
                    'border-color': '#f5c6cb',
                    'color': '#721c24'
                },
                'processed': {
                    'background-color': '#d1ecf1',
                    'border-color': '#bee5eb',
                    'color': '#0c5460'
                }
            };
            
            if (styles[status]) {
                dropdown.css(styles[status]);
                
                // Add a subtle animation
                dropdown.addClass('status-changing');
                setTimeout(() => {
                    dropdown.removeClass('status-changing');
                }, 300);
            }
        }

        // Function to show status change modal
        function showStatusChangeModal(leaveId, newStatus) {
            const statusMessages = {
                'approved': {
                    message: '{{__("You are about to approve this leave application. This action will mark the application as approved.")}}',
                    buttonClass: 'btn-success',
                    buttonText: '{{__("Approve Application")}}',
                    required: false
                },
                'rejected': {
                    message: '{{__("You are about to reject this leave application. Please provide a reason for rejection.")}}',
                    buttonClass: 'btn-danger',
                    buttonText: '{{__("Reject Application")}}',
                    required: true
                },
                'processed': {
                    message: '{{__("You are about to set this application as processing. This indicates the application is under review.")}}',
                    buttonClass: 'btn-warning',
                    buttonText: '{{__("Set as Processing")}}',
                    required: false
                }
            };
            
            const config = statusMessages[newStatus];
            if (!config) return;
            
            $('#leave_id').val(leaveId);
            $('#new_status').val(newStatus);
            $('#status-change-message').text(config.message);
            $('#catatan_validator').val('');
            
            const confirmBtn = $('#confirm-status-change');
            confirmBtn.removeClass('btn-primary btn-success btn-danger btn-warning')
                     .addClass(config.buttonClass)
                     .text(config.buttonText);
            
            // Handle required notes for rejection
            if (config.required) {
                $('#required-indicator').show();
                $('#catatan_validator').attr('required', true);
                $('#note-requirement').text('{{__("Notes are required for rejection")}}');
            } else {
                $('#required-indicator').hide();
                $('#catatan_validator').removeAttr('required');
                $('#note-requirement').text('{{__("Optional notes for this action")}}');
            }
            
            $('#StatusChangeModal').modal('show');
        }

        // Handle status change confirmation
        $('#confirm-status-change').on('click', function() {
            const leaveId = $('#leave_id').val();
            const newStatus = $('#new_status').val();
            const catatan = $('#catatan_validator').val().trim();
            
            // Validate required fields
            if (newStatus === 'rejected' && !catatan) {
                Swal.fire({
                    position: "center",
                    icon: "warning",
                    title: "{{__('Validation Error')}}",
                    text: "{{__('Notes are required for rejection')}}",
                    showConfirmButton: true
                });
                return;
            }
            
            const routes = {
                'approved': "{{route('leave-validation.approve')}}",
                'rejected': "{{route('leave-validation.reject')}}",
                'processed': "{{route('leave-validation.process')}}"
            };
            
            const actionTexts = {
                'approved': "{{__('Approving')}}",
                'rejected': "{{__('Rejecting')}}",
                'processed': "{{__('Processing')}}"
            };
            
            // Show loading state
            $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> ' + actionTexts[newStatus] + '...');
            
            $.ajax({
                url: routes[newStatus],
                type: "post",
                data: {
                    id: leaveId,
                    catatan_validator: catatan
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            position: "center",
                            icon: "success",
                            title: response.message,
                            showConfirmButton: false,
                            timer: 1500
                        });
                        $('#StatusChangeModal').modal('hide');
                        table.ajax.reload();
                    }
                },
                error: function(xhr) {
                    let message = "{{__('An error occurred while processing the request')}}";
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
                },
                complete: function() {
                    // Reset button state
                    $('#confirm-status-change').prop('disabled', false);
                }
            });
        });

        // Event handler for detail button
        $(document).on("click", ".detail", function() {
            let id = $(this).data('id');
            $.ajax({
                url: "{{route('leave-validation.detail')}}",
                type: "post",
                data: { id: id },
                success: function(response) {
                    if (response.success && response.data) {
                        const data = response.data;
                        let documentLink = '';
                        if (data.document_path) {
                            const documentUrl = "{{ asset('storage/') }}/" + data.document_path;
                            documentLink = `<p><strong>{{__('Document')}}:</strong> <a href="${documentUrl}" target="_blank" class="btn btn-sm btn-outline-primary">{{__('View Document')}}</a></p>`;
                        }

                        let approvedInfo = '';
                        if (data.approved_by && data.approver) {
                            approvedInfo = `
                                <p><strong>{{__('Approved By')}}:</strong> ${data.approver.name}</p>
                                <p><strong>{{__('Approved At')}}:</strong> ${data.approved_at ? new Date(data.approved_at).toLocaleString() : '-'}</p>
                            `;
                        }

                        let validatorNotes = '';
                        if (data.catatan_validator) {
                            validatorNotes = `<p><strong>{{__('Validator Notes')}}:</strong> ${data.catatan_validator}</p>`;
                        }

                        $('#detail-content').html(`
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>{{__('Code')}}:</strong> ${data.code}</p>
                                    <p><strong>{{__('Employee Name')}}:</strong> ${data.name}</p>
                                    <p><strong>{{__('Employee ID')}}:</strong> ${data.employee_id}</p>
                                    <p><strong>{{__('Application Date')}}:</strong> ${new Date(data.application_date).toLocaleDateString()}</p>
                                    <p><strong>{{__('Leave Type')}}:</strong> ${data.leave_type}</p>
                                    <p><strong>{{__('Status')}}:</strong> <span class="badge badge-${data.status === 'approved' ? 'success' : data.status === 'rejected' ? 'danger' : data.status === 'processed' ? 'info' : 'warning'}">${data.status}</span></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>{{__('Start Date')}}:</strong> ${new Date(data.start_date).toLocaleDateString()}</p>
                                    <p><strong>{{__('End Date')}}:</strong> ${new Date(data.end_date).toLocaleDateString()}</p>
                                    <p><strong>{{__('Total Days')}}:</strong> ${data.total_days}</p>
                                    <p><strong>{{__('User')}}:</strong> ${data.user ? data.user.name : 'N/A'}</p>
                                    ${approvedInfo}
                                </div>
                                <div class="col-12">
                                    <p><strong>{{__('Description')}}:</strong> ${data.description}</p>
                                    ${validatorNotes}
                                    ${documentLink}
                                </div>
                            </div>
                        `);
                        $('#DetailModal').modal('show');
                    }
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

        // Reset modal when closed
        $('#StatusChangeModal').on('hidden.bs.modal', function () {
            $('#status-change-form')[0].reset();
            $('#confirm-status-change').removeClass('btn-success btn-danger btn-warning').addClass('btn-primary').text('{{__("Confirm Change")}}');
            $('#required-indicator').hide();
        });

        // Initial binding for existing dropdowns
        bindStatusDropdownEvents();
    });
</script>
@endsection