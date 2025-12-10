<?php

namespace App\Notifications;

use App\Models\TindakLanjut;
use App\Models\User;
use App\Notifications\KasusButuhApprovalNotification;
use Illuminate\Support\Facades\Notification;

/**
 * Service untuk mengelola notifikasi terkait Tindak Lanjut.
 * 
 * Tanggung jawab:
 * - Kirim notifikasi ke Kepala Sekolah saat kasus butuh approval
 * - Kirim notifikasi ke Waka Kesiswaan untuk awareness (Surat 2)
 * - Handle notification logic secara terpusat
 * 
 * Design Pattern: Service Layer (Single Responsibility)
 */
class TindakLanjutNotificationService
{
    /**
     * Kirim notifikasi saat kasus baru dibuat yang butuh approval.
     * 
     * @param TindakLanjut $tindakLanjut
     * @return void
     */
    public function notifyKasusButuhApproval(TindakLanjut $tindakLanjut): void
    {
        // Hanya kirim notifikasi jika status "Menunggu Persetujuan"
        if ($tindakLanjut->status !== 'Menunggu Persetujuan') {
            return;
        }

        // Ambil Kepala Sekolah
        $kepalaSekolah = $this->getKepalaSekolah();
        
        if (!$kepalaSekolah) {
            \Log::warning('Kepala Sekolah tidak ditemukan untuk notifikasi approval', [
                'tindak_lanjut_id' => $tindakLanjut->id,
            ]);
            return;
        }

        // Kirim notifikasi (email + database)
        $kepalaSekolah->notify(new KasusButuhApprovalNotification($tindakLanjut));
    }

    /**
     * Kirim notifikasi awareness ke Waka Kesiswaan untuk Surat 2.
     * 
     * @param TindakLanjut $tindakLanjut
     * @return void
     */
    public function notifyWakaForSurat2(TindakLanjut $tindakLanjut): void
    {
        $surat = $tindakLanjut->suratPanggilan;
        
        // Hanya untuk Surat 2
        if (!$surat || $surat->tipe_surat !== 'Surat 2') {
            return;
        }

        $waka = $this->getWakaKesiswaan();
        
        if (!$waka) {
            return;
        }

        // TODO: Implement Surat2AwarenessNotification (Phase 2)
        // $waka->notify(new Surat2AwarenessNotification($tindakLanjut));
    }

    /**
     * Get Kepala Sekolah user.
     * 
     * @return User|null
     */
    private function getKepalaSekolah(): ?User
    {
        return User::whereHas('role', function ($q) {
            $q->where('nama_role', 'Kepala Sekolah');
        })->first();
    }

    /**
     * Get Waka Kesiswaan user.
     * 
     * @return User|null
     */
    private function getWakaKesiswaan(): ?User
    {
        return User::whereHas('role', function ($q) {
            $q->where('nama_role', 'Waka Kesiswaan');
        })->first();
    }

    /**
     * Get jumlah notifikasi yang belum dibaca untuk user.
     * 
     * @param User $user
     * @return int
     */
    public function getUnreadCount(User $user): int
    {
        return $user->unreadNotifications()->count();
    }

    /**
     * Mark notifikasi sebagai sudah dibaca.
     * 
     * @param User $user
     * @param string $notificationId
     * @return void
     */
    public function markAsRead(User $user, string $notificationId): void
    {
        $user->notifications()->where('id', $notificationId)->update(['read_at' => now()]);
    }

    /**
     * Mark semua notifikasi sebagai sudah dibaca.
     * 
     * @param User $user
     * @return void
     */
    public function markAllAsRead(User $user): void
    {
        $user->unreadNotifications->markAsRead();
    }
}
