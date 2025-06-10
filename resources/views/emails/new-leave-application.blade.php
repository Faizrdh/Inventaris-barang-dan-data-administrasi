<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Cuti Baru</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin: 20px 0;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }
        .info-item {
            margin-bottom: 10px;
        }
        .label {
            font-weight: bold;
            color: #555;
            display: block;
            margin-bottom: 5px;
        }
        .value {
            color: #333;
            background: white;
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #e0e0e0;
        }
        .description {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
        }
        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            background: #ffc107;
            color: #856404;
            border-radius: 20px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 12px;
        }
        .action-buttons {
            text-align: center;
            margin: 30px 0;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            margin: 5px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #666;
            font-size: 14px;
        }
        .urgent {
            background: #ffe6e6;
            border-left: 4px solid #e74c3c;
            padding: 15px;
            margin: 20px 0;
            border-radius: 0 8px 8px 0;
        }
        @media (max-width: 600px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
            .btn {
                display: block;
                margin: 10px 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📋 Pengajuan Cuti Baru</h1>
            <p>Memerlukan persetujuan Anda</p>
        </div>

        <div class="urgent">
            <strong>⚠️ PERHATIAN:</strong> Ada pengajuan cuti baru yang memerlukan review dan persetujuan Anda.
        </div>

        <div class="info-grid">
            <div class="info-item">
                <span class="label">📄 Kode Cuti:</span>
                <div class="value">{{ $leaveApplication->code }}</div>
            </div>
            <div class="info-item">
                <span class="label">👤 Nama Pegawai:</span>
                <div class="value">{{ $leaveApplication->name }}</div>
            </div>
            <div class="info-item">
                <span class="label">🆔 NIP:</span>
                <div class="value">{{ $leaveApplication->employee_id }}</div>
            </div>
            <div class="info-item">
                <span class="label">📧 Email:</span>
                <div class="value">{{ $leaveApplication->email }}</div>
            </div>
            <div class="info-item">
                <span class="label">📅 Tanggal Pengajuan:</span>
                <div class="value">{{ $leaveApplication->application_date->format('d F Y') }}</div>
            </div>
            <div class="info-item">
                <span class="label">🏷️ Jenis Cuti:</span>
                <div class="value">{{ $leaveApplication->leave_type }}</div>
            </div>
            <div class="info-item">
                <span class="label">📅 Tanggal Mulai:</span>
                <div class="value">{{ $leaveApplication->start_date->format('d F Y') }}</div>
            </div>
            <div class="info-item">
                <span class="label">📅 Tanggal Selesai:</span>
                <div class="value">{{ $leaveApplication->end_date->format('d F Y') }}</div>
            </div>
            <div class="info-item">
                <span class="label">📊 Total Hari:</span>
                <div class="value">{{ $leaveApplication->total_days }} hari</div>
            </div>
            <div class="info-item">
                <span class="label">🔄 Status:</span>
                <div class="value">
                    <span class="status-badge">{{ $leaveApplication->status }}</span>
                </div>
            </div>
        </div>

        @if($leaveApplication->description)
        <div class="description">
            <strong>📝 Deskripsi/Alasan:</strong><br>
            {{ $leaveApplication->description }}
        </div>
        @endif

        @if($leaveApplication->document_path)
        <div style="background: #e8f5e8; padding: 15px; border-radius: 8px; margin: 20px 0;">
            <strong>📎 Dokumen Pendukung:</strong><br>
            <a href="{{ asset('storage/' . $leaveApplication->document_path) }}" 
               target="_blank" 
               style="color: #28a745; text-decoration: none; font-weight: bold;">
                📄 Lihat Dokumen
            </a>
        </div>
        @endif

        <div class="action-buttons">
            <a href="{{ $approvalUrl }}" class="btn btn-primary">
                🔍 Review & Approve Pengajuan
            </a>
        </div>

        <div class="footer">
            <p>Email ini dikirim secara otomatis oleh Sistem Manajemen Cuti.</p>
            <p>Harap segera review pengajuan ini untuk kelancaran operasional.</p>
            <hr style="margin: 20px 0; border: none; border-top: 1px solid #eee;">
            <small>
                📍 {{ config('app.name') }} - Sistem Manajemen SDM<br>
                🕒 Dikirim pada: {{ now()->format('d F Y, H:i') }} WIB
            </small>
        </div>
    </div>
</body>
</html>