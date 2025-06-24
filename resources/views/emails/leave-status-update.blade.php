<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Status Pengajuan Cuti</title>
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
            padding: 20px;
            text-align: center;
            border-radius: 8px;
            margin-bottom: 30px;
            color: white;
        }
        .header.approved {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        }
        .header.rejected {
            background: linear-gradient(135deg, #dc3545 0%, #e74c3c 100%);
        }
        .header.processed {
            background: linear-gradient(135deg, #17a2b8 0%, #6f42c1 100%);
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .status-change {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
            text-align: center;
            border: 2px solid #dee2e6;
        }
        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 14px;
            margin: 0 10px;
        }
        .status-badge.approved {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .status-badge.rejected {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .status-badge.processed {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        .status-badge.pending {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        .arrow {
            font-size: 20px;
            color: #6c757d;
            margin: 0 10px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin: 20px 0;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
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
        .message-box {
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .message-box.approved {
            background: #d4edda;
            border-left: 4px solid #28a745;
            color: #155724;
        }
        .message-box.rejected {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
            color: #721c24;
        }
        .message-box.processed {
            background: #d1ecf1;
            border-left: 4px solid #17a2b8;
            color: #0c5460;
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
        .emoji-large {
            font-size: 48px;
            margin-bottom: 10px;
        }
        @media (max-width: 600px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
            .status-change {
                padding: 15px;
            }
            .arrow {
                display: block;
                margin: 10px 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header {{ $leaveApplication->status }}">
            <div class="emoji-large">
                @if($leaveApplication->status == 'approved')
                    ✅
                @elseif($leaveApplication->status == 'rejected')
                    ❌
                @elseif($leaveApplication->status == 'processed')
                    ⚙️
                @endif
            </div>
            <h1>Status Pengajuan Cuti Diperbarui</h1>
            <p>{{ $leaveApplication->name }}</p>
        </div>

        <div class="status-change">
            <h3>🔄 Perubahan Status</h3>
            <div>
                <span class="status-badge {{ $oldStatus }}">{{ ucfirst($oldStatus) }}</span>
                <span class="arrow">➡️</span>
                <span class="status-badge {{ $leaveApplication->status }}">{{ $statusText }}</span>
            </div>
        </div>

        <div class="message-box {{ $leaveApplication->status }}">
            @if($leaveApplication->status == 'approved')
                <strong>🎉 Selamat!</strong> Pengajuan cuti Anda telah <strong>DISETUJUI</strong>.<br>
                Anda dapat mengambil cuti sesuai dengan tanggal yang telah diajukan.
            @elseif($leaveApplication->status == 'rejected')
                <strong>😔 Maaf,</strong> Pengajuan cuti Anda telah <strong>DITOLAK</strong>.<br>
                Silakan hubungi supervisor untuk penjelasan lebih lanjut.
            @elseif($leaveApplication->status == 'processed')
                <strong>⚙️ Info:</strong> Pengajuan cuti Anda sedang <strong>DIPROSES</strong>.<br>
                Mohon tunggu update selanjutnya.
            @endif
        </div>

        <div class="info-grid">
            <div class="info-item">
                <span class="label">📄 Kode Cuti:</span>
                <div class="value">{{ $leaveApplication->code }}</div>
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
                <span class="label">📅 Diproses pada:</span>
                <div class="value">{{ $leaveApplication->approved_at ? $leaveApplication->approved_at->format('d F Y, H:i') : '-' }} WIB</div>
            </div>
        </div>

        @if($leaveApplication->catatan_validator)
        <div class="message-box approved">
            <strong>📝 Catatan dari Validator:</strong><br>
            {{ $leaveApplication->catatan_validator }}
        </div>
        @endif

        <div class="action-buttons">
            <a href="{{ $dashboardUrl }}" class="btn btn-primary">
                📊 Lihat Dashboard Cuti
            </a>
        </div>

        <div class="footer">
            <p>Email ini dikirim secara otomatis oleh Sistem Manajemen Cuti.</p>
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