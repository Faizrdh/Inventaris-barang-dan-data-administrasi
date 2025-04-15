@extends('layouts.app')
@section('title',__('pengajuan cuti'))
@section('content')
<x-head-datatable/>
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card w-100">
                <div class="card-header row">
                    <div class="d-flex justify-content-end align-items-center w-100">
                        <button class="btn btn-success" type="button" data-toggle="modal" data-target="#TambahData" id="modal-button"><i class="fas fa-plus m-1"></i> {{__('tambah data')}}</button>
                    </div>
                </div>

                <!-- Modal Pengajuan Cuti -->
                <div class="modal fade" id="TambahData" tabindex="-1" aria-labelledby="TambahDataModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="TambahDataModalLabel">{{__("create leave application")}}</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="kode" class="form-label">{{__("kode cuti")}}<span class="text-danger">*</span></label>
                                        <input type="text" name="kode" readonly class="form-control">
                                        <input type="hidden" name="id"/>
                                    </div>
                                    <!-- Tambahan form Nama -->
                                    <div class="form-group">
                                        <label for="nama" class="form-label">{{__("Nama")}}<span class="text-danger">*</span></label>
                                        <input type="text" name="nama" class="form-control">
                                    </div>
                                    <!-- Tambahan form NIP -->
                                    <div class="form-group">
                                        <label for="nip" class="form-label">{{__("NIP")}}<span class="text-danger">*</span></label>
                                        <input type="text" name="nip" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label for="tanggal_pengajuan" class="form-label">{{__("tanggal pengajuan")}} <span class="text-danger">*</span></label>
                                        <input type="date" name="tanggal_pengajuan" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="jenis_cuti" class="form-label">{{__("tipe izin")}}<span class="text-danger">*</span></label>
                                        <select name="jenis_cuti" class="form-control">
                                            <option selected value="">-- {{__("pilih 1 tipe izin")}} --</option>
                                            <option value="Cuti Tahunan">{{__("Cuti Tahunan")}}</option>
                                            <option value="Cuti Sakit">{{__("Cuti Sakit")}}</option>
                                            <option value="CAP">{{__("Cuti Alasan Penting")}}</option>
                                            <option value="Cuti Melahirkan">{{__("Cuti Melahirkan")}}</option>
                                            <option value="CLTN">{{__("CLTN")}}</option>
                                            <option value="Izin">{{__("Izin")}}</option>
                                            <option value="Sakit">{{__("Sakit")}}</option>
                                            <option value="Dinas Luar">{{__("Dinas Luar")}}</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="tanggal_mulai" class="form-label">{{__("start date")}} <span class="text-danger">*</span></label>
                                        <input type="date" name="tanggal_mulai" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label for="tanggal_selesai" class="form-label">{{__("end date")}} <span class="text-danger">*</span></label>
                                        <input type="date" name="tanggal_selesai" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label for="total_hari" class="form-label">{{__("total hari")}}</label>
                                        <input type="number" name="total_hari" readonly class="form-control">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="keterangan" class="form-label">{{__("description")}}<span class="text-danger">*</span></label>
                                <textarea name="keterangan" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="dokumen" class="form-label">{{__("Dokumen Pendukung")}} (PDF)</label>
                                <input type="file" name="dokumen" class="form-control-file" accept=".pdf">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal" id="kembali">{{__("cancel")}}</button>
                            <button type="button" class="btn btn-success" id="simpan">{{__("save")}}</button>
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
                                    <!-- Tambahan kolom Nama di tabel -->
                                    <th class="border-bottom-0">{{__("Nama")}}</th>
                                    <!-- Tambahan kolom NIP di tabel -->
                                    <th class="border-bottom-0">{{__("NIP")}}</th>
                                    <th class="border-bottom-0">{{__("Tanggal Pengajuan")}}</th>
                                    <th class="border-bottom-0">{{__("Tipe Izin")}}</th>
                                    <th class="border-bottom-0">{{__("Tanggal Mulai")}}</th>
                                    <th class="border-bottom-0">{{__("Tanggal Selesai")}}</th>
                                    <th class="border-bottom-0">{{__("total Hari")}}</th>
                                    <th class="border-bottom-0">{{__("status")}}</th>
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

  // Perbaikan fungsi hitungTotalHari
function hitungTotalHari() {
    const tanggalMulai = $("input[name='tanggal_mulai']").val();
    const tanggalSelesai = $("input[name='tanggal_selesai']").val();
    
    if (tanggalMulai && tanggalSelesai) {
        const start = new Date(tanggalMulai);
        const end = new Date(tanggalSelesai);
        
        // Check if end date is before start date
        if (end < start) {
            Swal.fire({
                position: "center",
                icon: "warning",
                title: "Tanggal tidak valid",
                text: "Tanggal selesai tidak boleh sebelum tanggal mulai",
                showConfirmButton: true
            });
            $("input[name='tanggal_selesai']").val('');
            $("input[name='total_hari']").val('');
            return;
        }
        
        // Calculate difference in days including the start day
        const diffTime = Math.abs(end - start);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
        $("input[name='total_hari']").val(diffDays);
    }
}

// Perbaikan pada fungsi generateKodeCuti
function generateKodeCuti() {
    const currentDate = new Date();
    const year = currentDate.getFullYear().toString().substr(-2);
    const month = (currentDate.getMonth() + 1).toString().padStart(2, '0');
    const random = Math.floor(Math.random() * 9000 + 1000);
    const kode = `CT-${year}${month}-${random}`;
    $("input[name='kode']").val(kode);
}

    function generateKodeCuti() {
        const currentDate = new Date();
        const year = currentDate.getFullYear().toString().substr(-2);
        const month = (currentDate.getMonth() + 1).toString().padStart(2, '0');
        const random = Math.floor(Math.random() * 9000 + 1000);
        const kode = `CT-${year}${month}-${random}`;
        $("input[name='kode']").val(kode);
    }

    function simpan() {
        const id = $("input[name='id']").val();
        const user_id = "{{ Auth::user()->id }}";
        const kode = $("input[name='kode']").val();
        // Tambahan variabel untuk Nama dan NIP
        const nama = $("input[name='nama']").val();
        const nip = $("input[name='nip']").val();
        const tanggal_pengajuan = $("input[name='tanggal_pengajuan']").val();
        const jenis_cuti = $("select[name='jenis_cuti']").val();
        const tanggal_mulai = $("input[name='tanggal_mulai']").val();
        const tanggal_selesai = $("input[name='tanggal_selesai']").val();
        const total_hari = $("input[name='total_hari']").val();
        const keterangan = $("textarea[name='keterangan']").val();
        
        const Form = new FormData();
        Form.append('user_id', user_id);
        Form.append('kode', kode);
        // Tambahan Form.append untuk Nama dan NIP
        Form.append('nama', nama);
        Form.append('nip', nip);
        Form.append('tanggal_pengajuan', tanggal_pengajuan);
        Form.append('jenis_cuti', jenis_cuti);
        Form.append('tanggal_mulai', tanggal_mulai);
        Form.append('tanggal_selesai', tanggal_selesai);
        Form.append('total_hari', total_hari);
        Form.append('keterangan', keterangan);
        Form.append('status', 'pending');

        if ($("input[name='dokumen']")[0].files[0]) {
            Form.append('dokumen', $("input[name='dokumen']")[0].files[0]);
        }
        
        $.ajax({
            url: "{{route('leave-application.save')}}",
            type: "post",
            processData: false,
            contentType: false,
            dataType: 'json',
            data: Form,
            success: function(res) {
                Swal.fire({
                    position: "center",
                    icon: "success",
                    title: res.message,
                    showConfirmButton: false,
                    timer: 1500
                });
                $('#kembali').click();
                resetForm();
                $('#data-tabel').DataTable().ajax.reload();
            },
            error: function(xhr) {
                let message = "Terjadi kesalahan saat menyimpan data";
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
    }

    function ubah() {
        const id = $("input[name='id']").val();
        const kode = $("input[name='kode']").val();
        // Tambahan variabel untuk Nama dan NIP
        const nama = $("input[name='nama']").val();
        const nip = $("input[name='nip']").val();
        const tanggal_pengajuan = $("input[name='tanggal_pengajuan']").val();
        const jenis_cuti = $("select[name='jenis_cuti']").val();
        const tanggal_mulai = $("input[name='tanggal_mulai']").val();
        const tanggal_selesai = $("input[name='tanggal_selesai']").val();
        const total_hari = $("input[name='total_hari']").val();
        const keterangan = $("textarea[name='keterangan']").val();
        
        const Form = new FormData();
        Form.append('id', id);
        Form.append('kode', kode);
        // Tambahan Form.append untuk Nama dan NIP
        Form.append('nama', nama);
        Form.append('nip', nip);
        Form.append('tanggal_pengajuan', tanggal_pengajuan);
        Form.append('jenis_cuti', jenis_cuti);
        Form.append('tanggal_mulai', tanggal_mulai);
        Form.append('tanggal_selesai', tanggal_selesai);
        Form.append('total_hari', total_hari);
        Form.append('keterangan', keterangan);

        if ($("input[name='dokumen']")[0].files[0]) {
            Form.append('dokumen', $("input[name='dokumen']")[0].files[0]);
        }
        
        $.ajax({
            url: "{{route('leave-application.update')}}",
            type: "post",
            processData: false,
            contentType: false,
            dataType: 'json',
            data: Form,
            success: function(res) {
                Swal.fire({
                    position: "center",
                    icon: "success",
                    title: res.message,
                    showConfirmButton: false,
                    timer: 1500
                });
                $('#kembali').click();
                resetForm();
                $('#data-tabel').DataTable().ajax.reload();
            },
            error: function(xhr) {
                let message = "Terjadi kesalahan saat memperbarui data";
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
    }

    function resetForm() {
        $("input[name='id']").val('');
        $("input[name='kode']").val('');
        // Tambahan reset form untuk Nama dan NIP
        $("input[name='nama']").val('');
        $("input[name='nip']").val('');
        $("input[name='tanggal_pengajuan']").val('');
        $("select[name='jenis_cuti']").val('');
        $("input[name='tanggal_mulai']").val('');
        $("input[name='tanggal_selesai']").val('');
        $("input[name='total_hari']").val('');
        $("textarea[name='keterangan']").val('');
        $("input[name='dokumen']").val('');
    }

    $(document).ready(function() {
        $('#data-tabel').DataTable({
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
                    data: "kode",
                    name: "kode"
                },
                // Tambahan kolom Nama di DataTable
                {
                    data: "nama",
                    name: "nama"
                },
                // Tambahan kolom NIP di DataTable
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
                    data: "status",
                    name: "status",
                    render: function(data) {
                        let badgeClass = 'badge-warning';
                        if (data == 'approved') badgeClass = 'badge-success';
                        if (data == 'rejected') badgeClass = 'badge-danger';
                        return `<span class="badge ${badgeClass}">${data}</span>`;
                    }
                },
                {
                    data: "tindakan",
                    name: "tindakan"
                }
            ]
        });

        // Event handler untuk tanggal
        $("input[name='tanggal_mulai'], input[name='tanggal_selesai']").on('change', hitungTotalHari);

        // Event handler untuk tombol tambah
        $('#modal-button').on('click', function() {
            resetForm();
            generateKodeCuti();
            // Set tanggal pengajuan ke hari ini
            const today = new Date().toISOString().split('T')[0];
            $("input[name='tanggal_pengajuan']").val(today);
            $('#TambahDataModalLabel').text("{{__('create leave application')}}");
            $('#simpan').text("{{__('save')}}");
        });

        // Event handler untuk tombol simpan
        $('#simpan').on('click', function() {
            // Basic form validation
            const required_fields = ['nama', 'nip', 'tanggal_pengajuan', 'jenis_cuti', 'tanggal_mulai', 'tanggal_selesai', 'keterangan'];
            let isValid = true;
            
            for (let i = 0; i < required_fields.length; i++) {
                const field = required_fields[i];
                const value = field === 'jenis_cuti' ? $(`select[name='${field}']`).val() : $(`[name='${field}']`).val();
                if (!value || value === '') {
                    isValid = false;
                    const fieldLabel = $(`label[for='${field}']`).text().replace('*', '').trim();
                    Swal.fire({
                        position: "center",
                        icon: "warning",
                        title: "Form tidak lengkap",
                        text: `${fieldLabel} harus diisi`,
                        showConfirmButton: true
                    });
                    break;
                }
            }
            
            if (!isValid) return;
            
            // If validation passes, proceed with save or update
            if ($("input[name='id']").val()) {
                ubah();
            } else {
                simpan();
            }
        });
    });

    // Event handler untuk tombol ubah
    $(document).on("click", ".ubah", function() {
        let id = $(this).attr('id');
        $.ajax({
            url: "{{route('leave-application.detail')}}",
            type: "post",
            data: {
                id: id,
            },
            success: function({data}) {
                $('#TambahData').modal('show');
                $('#TambahDataModalLabel').text("{{__('edit leave application')}}");
                $('#simpan').text("{{__('save changes')}}");
                
                $("input[name='id']").val(data.id);
                $("input[name='kode']").val(data.kode);
                $("input[name='nama']").val(data.nama);
                $("input[name='nip']").val(data.nip);
                $("input[name='tanggal_pengajuan']").val(data.tanggal_pengajuan);
                $("select[name='jenis_cuti']").val(data.jenis_cuti);
                $("input[name='tanggal_mulai']").val(data.tanggal_mulai);
                $("input[name='tanggal_selesai']").val(data.tanggal_selesai);
                $("input[name='total_hari']").val(data.total_hari);
                $("textarea[name='keterangan']").val(data.keterangan);
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

 // Tambahkan pada $(document).ready
// Event handler untuk tombol Edit (completion dari kode yang belum selesai)
$(document).on("click", ".hapus", function() {
    let id = $(this).attr('id');
    const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
            confirmButton: "btn btn-success m-1",
            cancelButton: "btn btn-danger m-1"
        },
        buttonsStyling: false
    });
    swalWithBootstrapButtons.fire({
        title: "Anda Yakin ?",
        text: "Data Ini Akan Di Hapus",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Ya, Hapus",
        cancelButtonText: "Tidak, Kembali!",
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{route('leave-application.delete')}}",
                type: "delete",
                data: {
                    id: id
                },
                success: function(res) {
                    Swal.fire({
                        position: "center",
                        icon: "success",
                        title: res.message,
                        showConfirmButton: false,
                        timer: 1500
                    });
                    $('#data-tabel').DataTable().ajax.reload();
                },
                error: function(xhr) {
                    let message = "Terjadi kesalahan saat menghapus data";
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
        }
    });
});

// Tambahkan event handler untuk melihat detail
$(document).on("click", ".detail", function() {
    let id = $(this).attr('id');
    $.ajax({
        url: "{{route('leave-application.detail')}}",
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
</script>
@endsection