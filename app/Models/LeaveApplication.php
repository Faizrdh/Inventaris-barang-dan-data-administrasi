<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class LeaveApplication extends Model
{
    use HasFactory;

    // Menentukan nama tabel yang benar
    protected $table = 'cutis';
    
    // Menentukan koneksi database yang benar
    protected $connection = 'db_inventaris';

    // Fillable columns for mass-assignment, disesuaikan dengan kolom di tabel 'cutis'
    protected $fillable = [
        'kode',
        'nama',
        'nip',
        'tanggal_pengajuan',
        'tanggal_mulai',
        'tanggal_selesai',
        'total_hari',
        'keterangan',
        'status',
        'file_izin', // Sesuaikan dengan nama di tabel cutis (bukan dokumen)
        'approved_by',
        'approved_at',
    ];

    // Convert column data types to appropriate formats
    protected $casts = [
        'tanggal_pengajuan' => 'date', // Sesuaikan dengan tipe di migrasi (date, bukan datetime)
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'approved_at' => 'datetime',
    ];

    /**
     * Relation to User model for approved leave applications
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Calculate total days based on start and end dates
     */
    public function hitungTotalHari()
    {
        if ($this->tanggal_mulai && $this->tanggal_selesai) {
            $start = new \DateTime($this->tanggal_mulai);
            $end = new \DateTime($this->tanggal_selesai);
            $diff = $start->diff($end);
            return $diff->days + 1; // Total days including first day
        }

        return 0;
    }

    /**
     * Save document file when leave application is submitted
     *
     * @param \Illuminate\Http\UploadedFile|null $file
     * @return string|null
     */
    public function saveDokumen($file)
    {
        if ($file) {
            return $file->store('cuti_files', 'public'); // Store file in 'cuti_files' folder in public storage
        }

        return null;
    }

    /**
     * Delete document file associated with leave application
     *
     * @return void
     */
    public function deleteDokumen()
    {
        if ($this->file_izin && Storage::disk('public')->exists($this->file_izin)) {
            Storage::disk('public')->delete($this->file_izin);
        }
    }
}