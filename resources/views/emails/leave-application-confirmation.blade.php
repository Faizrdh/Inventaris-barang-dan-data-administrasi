<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Pengajuan Cuti</title>
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
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
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
        .confirmation-box {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
        }
        .confirmation-box .emoji {
            font-size: 48px;
            margin-bottom: 10px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin: 20px 0;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #28a745;
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
        .description {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
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
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
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
        .next-steps {
            background: #e8f4fd;
            border-left: 4px solid #17a2b8;
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
            <h1>✅ Konfirmasi Pengajuan Cuti</h1>
            <p>{{ $application->name }}</p>
        </div>

        <div class="confirmation-box">
            <div class="emoji">🎉</div>
            <h3>Pengajuan Cuti Berhasil Dikirim!</h3>
            <p>Terima kasih, pengajuan cuti Anda telah berhasil diterima dan akan segera diproses.</p>
        </div>

        <div class="info-grid">
            <div class="info-item">
                <span class="label">📄 Kode Cuti:</span>
                <div class="value">{{ $application->code }}</div>
            </div>
            <div class="info-item">
                <span class="label">👤 Nama Pegawai:</span>
                <div class="value">{{ $application->name }}</div>
            </div>
            <div class="info-item">
                <span class="label">🆔 NIP:</span>
                <div class="value">{{ $application->employee_id }}</div>
            </div>
            <div class="info-item">
                <span class="label">📧 Email:</span>
                <div class="value">{{ $application->email }}</div>
            </div>
            <div class="info-item">
                <span class="label">📅 Tanggal Pengajuan:</span>
                <div class="value">{{ $application->application_date->format('d F Y') }}</div>
            </div>
            <div class="info-item">
                <span class="label">🏷️ Jenis Cuti:</span>
                <div class="value">{{ $application->leave_type }}</div>
            </div>
            <div class="info-item">
                <span class="label">📅 Tanggal Mulai:</span>
                <div class="value">{{ $application->start_date->format('d F Y') }}</div>
            </div>
            <div class="info-item">
                <span class="label">📅 Tanggal Selesai:</span>
                <div class="value">{{ $application->end_date->format('d F Y') }}</div>
            </div>
            <div class="info-item">
                <span class="label">📊 Total Hari:</span>
                <div class="value">{{ $application->total_days }} hari</div>
            </div>
            <div class="info-item">
                <span class="label">🔄 Status:</span>
                <div class="value">
                    <span class="status-badge">{{ ucfirst($application->status) }}</span>
                </div>
            </div>
        </div>

        @if($application->description)
        <div class="description">
            <strong>📝 Deskripsi/Alasan:</strong><br>
            {{ $application->description }}
        </div>
        @endif

        @if($application->document_path)
        <div style="background: #e8f5e8; padding: 15px; border-radius: 8px; margin: 20px 0;">
            <strong>📎 Dokumen Pendukung:</strong><br>
            <a href="{{ asset('storage/' . $application->document_path) }}" 
               target="_blank" 
               style="color: #28a745; text-decoration: none; font-weight: bold;">
                📄 Lihat Dokumen
            </a>
        </div>
        @endif

        <div class="next-steps">
            <strong>📋 Langkah Selanjutnya:</strong><br>
            1. Pengajuan Anda akan direview oleh supervisor<br>
            2. Anda akan mendapat email notifikasi saat status berubah<br>
            3. Pantau status pengajuan melalui dashboard
        </div>

        <div class="action-buttons">
            <a href="{{ $dashboardUrl }}" class="btn btn-primary">
                📊 Lihat Status Pengajuan
            </a>
        </div>

        <div class="footer">
            <p>Email konfirmasi ini dikirim secara otomatis oleh Sistem Manajemen Cuti.</p>
            <p>Jika ada pertanyaan, silakan hubungi bagian SDM.</p>
            <hr style="margin: 20px 0; border: none; border-top: 1px solid #eee;">
            <small>
                📍 {{ config('app.name') }} - Sistem Manajemen SDM<br>
                🕒 Dikirim pada: {{ now()->format('d F Y, H:i') }} WIB
            </small>
        </div>
    </div>
</body>
</html>