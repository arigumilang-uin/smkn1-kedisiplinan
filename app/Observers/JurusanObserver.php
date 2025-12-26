<?php

namespace App\Observers;

use App\Models\Jurusan;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Jurusan Observer
 * 
 * PURPOSE: Auto-update Kaprodi name when assigned to Jurusan
 */
class JurusanObserver
{
    /**
     * Handle the Jurusan "created" event.
     */
    public function created(Jurusan $jurusan): void
    {
        Log::info('JurusanObserver::created called', ['jurusan_id' => $jurusan->id]);
        $this->syncKaprodiName($jurusan);
    }

    /**
     * Handle the Jurusan "updated" event.
     */
    public function updated(Jurusan $jurusan): void
    {
        Log::info('JurusanObserver::updated called', [
            'jurusan_id' => $jurusan->id,
            'wasChanged_kaprodi' => $jurusan->wasChanged('kaprodi_user_id'),
            'wasChanged_nama' => $jurusan->wasChanged('nama_jurusan'),
            'new_kaprodi_id' => $jurusan->kaprodi_user_id,
            'old_kaprodi_id' => $jurusan->getOriginal('kaprodi_user_id'),
        ]);
        
        // ALWAYS sync nama when jurusan has kaprodi (regardless of what changed)
        // This ensures nama is always correct
        $this->syncKaprodiName($jurusan);
        
        // If kaprodi changed, also update old kaprodi name
        if ($jurusan->wasChanged('kaprodi_user_id')) {
            $oldKaprodiId = $jurusan->getOriginal('kaprodi_user_id');
            if ($oldKaprodiId) {
                $oldKaprodi = User::find($oldKaprodiId);
                if ($oldKaprodi && str_starts_with($oldKaprodi->nama, 'Kaprodi ')) {
                    // Reset to just "Kaprodi" or role name
                    $oldKaprodi->nama = $oldKaprodi->role->nama_role ?? 'Kaprodi';
                    $oldKaprodi->saveQuietly();
                    Log::info('Reset old kaprodi name', ['user_id' => $oldKaprodiId, 'new_name' => $oldKaprodi->nama]);
                }
            }
        }
    }
    
    /**
     * Sync Kaprodi name with Jurusan
     * 
     * EXCLUSION: Developer role is NEVER auto-synced
     */
    private function syncKaprodiName(Jurusan $jurusan): void
    {
        Log::info('syncKaprodiName called', [
            'jurusan_id' => $jurusan->id,
            'kaprodi_user_id' => $jurusan->kaprodi_user_id,
            'nama_jurusan' => $jurusan->nama_jurusan,
        ]);
        
        if ($jurusan->kaprodi_user_id) {
            $kaprodi = User::find($jurusan->kaprodi_user_id);
            
            if ($kaprodi) {
                // SKIP auto-sync for Developer role
                if ($kaprodi->role && $kaprodi->role->nama_role === 'Developer') {
                    Log::info('Skipping Developer role');
                    return; // Developer names stay as-is
                }
                
                $newName = "Kaprodi {$jurusan->nama_jurusan}";
                Log::info('Setting new kaprodi name', [
                    'user_id' => $kaprodi->id,
                    'old_name' => $kaprodi->nama,
                    'new_name' => $newName,
                ]);
                
                if ($kaprodi->nama !== $newName) {
                    $kaprodi->nama = $newName;
                    $kaprodi->saveQuietly();
                    Log::info('Kaprodi name updated successfully');
                }
            } else {
                Log::warning('Kaprodi user not found', ['kaprodi_user_id' => $jurusan->kaprodi_user_id]);
            }
        }
    }
}
