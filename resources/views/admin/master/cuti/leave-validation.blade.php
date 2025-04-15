@extends('layouts.app')
@section('title',__('validasi pengajuan cuti'))
@section('content')
<x-head-datatable/>
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card w-100">
                <div class="card-header row">
                    <div class="d-flex justify-content-between align-items-center w-100">
                        <h4>{{__('Daftar Pengajuan Cuti Yang Perlu Divalidasi')}}</h4>
                    </div>
                </div>

                <!-- Modal Validasi Cuti -->
                <div class="modal fade" id="ValidasiModal" tabindex="-1" aria-labelledby="ValidasiModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="ValidasiModalLabel">{{__("validasi pengajuan cuti")}}</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="id">
                            <input type="hidden" name="action" value="approve">
                            
                            <div class="form-group">
                                <label for="catatan" class="form-label">{{__("Catatan/Keterangan")}}</label>
                                <textarea name="catatan" class="form-control" rows="3"></textarea>
                                <small class="text-muted catatan-help">{{__("Opsional untuk persetujuan, wajib untuk penolakan")}}</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">{{__("cancel")}}</button>
                            <button type="button" class="btn btn-success" id="confirmValidasi">{{__("confirm")}}</button>
                        </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table id="data-tabel" width="100%" class="table table-bordered text-nowrap border-bottom dataTable no-footer dtr-inline collapsed">
                            <thead>
                                <tr>
                                    <th class="border-bottom-0" width="5%">{{__("no")}}</th>
                                    <th class="border-bottom-0">{{__("Kode Cuti")}}</th>
                                    <th class="border-bottom-0">{{__("Nama")}}</th>
                                    <th class="border-bottom-0">{{__("NIP")}}</th>
                                    <th class="border-bottom-0">{{__("Tanggal Pengajuan")}}</th>
                                    <th class="border-bottom-0">{{__("Tipe Izin")}}</th>
                                    <th class="border-bottom-0">{{__("Tanggal Mulai")}}</th>
                                    <th class="border-bottom-0">{{__("Tanggal Selesai")}}</th>
                                    <th class="border-bottom-0">{{__("total Hari")}}</th>
                                    <th class="border-bottom-0" width="1%">{{__("action")}}</th>
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

    $(document).ready(function() {
        $('#data-tabel').DataTable({
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
                    data: "kode",
                    name: "kode"
                },
                {
                    data: "nama",
                    name: "nama"
                },
                {
                    data: "nip",
                    name: "nip"
                },
                {
                    data: "tanggal_pengajuan",
                    name: "tanggal_pengajuan"
                },
                {
                    data: "jenis_cuti",
                    name: "jenis_cuti"
                },
                {
                    data: "tanggal_mulai",
                    name: "tanggal_mulai"
                },
                {
                    data: "tanggal_selesai",
                    name: "tanggal_selesai"
                },
                {
                    data: "total_hari",
                    name: "total_hari"
                },
                {
                    data: "tindakan",
                    name: "tindakan"
                }
            ]
        });

        // Event handler untuk tombol validasi
        $(document).on("click", ".validasi", function() {
            let id = $(this).attr('id');
            $("input[name='id']").val(id);
            $("input[name='action']").val('approve');
            $("#ValidasiModalLabel").text("{{__('Setujui Pengajuan Cuti')}}");
            $("#confirmValidasi").removeClass('btn-danger').addClass('btn-success').text("{{__('Setujui')}}");
            $(".catatan-help").text("{{__('Opsional untuk persetujuan')}}");
            $("textarea[name='catatan']").val('').prop('required', false);
            $('#ValidasiModal').modal('show');
        });

        // Event handler untuk tombol tolak
        $(document).on("click", ".tolak", function() {
            let id = $(this).attr('id');
            $("input[name='id']").val(id);
            $("input[name='action']").val('reject');
            $("#ValidasiModalLabel").text("{{__('Tolak Pengajuan Cuti')}}");
            $("#confirmValidasi").removeClass('btn-success').addClass('btn-danger').text("{{__('Tolak')}}");
            $(".catatan-help").text("{{__('Wajib diisi untuk penolakan')}}");
            $("textarea[name='catatan']").val('').prop('required', true);
            $('#ValidasiModal').modal('show');
        });

        // Event handler untuk tombol konfirmasi validasi
        $('#confirmValidasi').on('click', function() {
            const id = $("input[name='id']").val();
            const action = $("input[name='action']").val();
            const catatan = $("textarea[name='catatan']").val();
            
            // Validasi catatan untuk penolakan
            if (action === 'reject' && !catatan) {
                Swal.fire({
                    position: "center",
                    icon: "warning",
                    title: "Form tidak lengkap",
                    text: "Catatan wajib diisi untuk penolakan",
                    showConfirmButton: true
                });
                return;
            }
            
            let url = action === 'approve' 
                ? "{{route('leave-validation.approve')}}" 
                : "{{route('leave-validation.reject')}}";
                
            $.ajax({
                url: url,
                type: "post",
                data: {
                    id: id,
                    catatan: catatan
                },
                success: function(res) {
                    Swal.fire({
                        position: "center",
                        icon: "success",
                        title: res.message,
                        showConfirmButton: false,
                        timer: 1500
                    });
                    $('#ValidasiModal').modal('hide');
                    $('#data-tabel').DataTable().ajax.reload();
                },
                error: function(xhr) {
                    let message = "Terjadi kesalahan saat memproses data";
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        position: "center",
                        icon: "error",
                        title: "Gagal!",
                        text: message,
                        showConfirmButton: true
                    });
                }
            });
        });

        // Event handler untuk melihat detail
        $(document).on("click", ".detail", function() {
            let id = $(this).attr('id');
            $.ajax({
                url: "{{route('leave-validation.detail')}}",
                type: "post",
                data: {
                    id: id,
                },
                success: function({data}) {
                    Swal.fire({
                        title: 'Detail Pengajuan Cuti',
                        html: `
                            <div class="text-left">
                                <p><strong>Kode:</strong> ${data.kode}</p>
                                <p><strong>Nama:</strong> ${data.nama}</p>
                                <p><strong>NIP:</strong> ${data.nip}</p>
                                <p><strong>Tanggal Pengajuan:</strong> ${data.tanggal_pengajuan}</p>
                                <p><strong>Jenis Cuti:</strong> ${data.jenis_cuti}</p>
                                <p><strong>Tanggal Mulai:</strong> ${data.tanggal_mulai}</p>
                                <p><strong>Tanggal Selesai:</strong> ${data.tanggal_selesai}</p>
                                <p><strong>Total Hari:</strong> ${data.total_hari}</p>
                                <p><strong>Keterangan:</strong> ${data.keterangan}</p>
                                <p><strong>Status:</strong> ${data.status}</p>
                            </div>
                        `,
                        confirmButtonText: 'Tutup',
                        width: '600px'
                    });
                },
                error: function(xhr) {
                    let message = "Terjadi kesalahan saat mengambil data";
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        position: "center",
                        icon: "error",
                        title: "Gagal!",
                        text: message,
                        showConfirmButton: true
                    });
                }
            });
        });
    });
</script>
@endsection