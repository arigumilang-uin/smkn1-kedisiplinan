<?php

namespace App\Http\Controllers\Rules;

use App\Http\Controllers\Controller;

use App\Models\JenisPelanggaran;
use App\Models\PelanggaranFrequencyRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FrequencyRulesController extends Controller
{
    /**
     * Display list semua jenis pelanggaran dengan toggle frequency rules.
     */
    public function index(Request $request)
    {
        $kategoriId = $request->get('kategori_id');
        
        $query = JenisPelanggaran::with([
            'kategoriPelanggaran',
            'frequencyRules' => function ($q) {
                $q->orderBy('display_order');
            }
        ]);
        
        if ($kategoriId) {
            $query->where('kategori_id', $kategoriId);
        }
        
        $jenisPelanggaran = $query->orderBy('kategori_id')->orderBy('nama_pelanggaran')->get();
        
        // Get all kategori for filter dropdown
        $kategoris = \App\Models\KategoriPelanggaran::orderBy('nama_kategori')->get();
        
        return view('frequency-rules.index', compact('jenisPelanggaran', 'kategoris', 'kategoriId'));
    }

    /**
     * Display detail frequency rules untuk satu jenis pelanggaran.
     */
    public function show($jenisPelanggaranId)
    {
        $jenisPelanggaran = JenisPelanggaran::with([
            'kategoriPelanggaran',
            'frequencyRules' => function ($q) {
                $q->orderBy('display_order');
            }
        ])->findOrFail($jenisPelanggaranId);
        
        return view('frequency-rules.show', compact('jenisPelanggaran'));
    }

    /**
     * Toggle is_active status untuk jenis pelanggaran.
     */
    public function toggleActive(Request $request, $jenisPelanggaranId)
    {
        $jenisPelanggaran = JenisPelanggaran::findOrFail($jenisPelanggaranId);
        
        // Validasi: tidak bisa aktif jika belum ada rules
        if (!$jenisPelanggaran->is_active && $jenisPelanggaran->frequencyRules->count() == 0) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak bisa mengaktifkan pelanggaran tanpa rules. Tambahkan rules terlebih dahulu.'
            ], 400);
        }
        
        $jenisPelanggaran->is_active = !$jenisPelanggaran->is_active;
        $jenisPelanggaran->save();
        
        return response()->json([
            'success' => true,
            'is_active' => $jenisPelanggaran->is_active,
            'message' => $jenisPelanggaran->is_active 
                ? 'Pelanggaran diaktifkan' 
                : 'Pelanggaran dinonaktifkan'
        ]);
    }

    /**
     * Store new frequency rule.
     */
    public function store(Request $request, $jenisPelanggaranId)
    {
        $jenisPelanggaran = JenisPelanggaran::findOrFail($jenisPelanggaranId);
        
        $validated = $request->validate([
            'frequency_min' => 'nullable|integer|min:1',
            'frequency_max' => 'nullable|integer|gte:frequency_min',
            'poin' => 'required|integer|min:0',
            'sanksi_description' => 'required|string|max:500',
            'trigger_surat' => 'nullable|boolean',
            'pembina_roles' => 'required|array|min:1',
            'pembina_roles.*' => 'string',
            'display_order' => 'nullable|integer|min:1',
        ]);
        
        // Default values
        $validated['frequency_min'] = $validated['frequency_min'] ?? 1;
        $validated['trigger_surat'] = $validated['trigger_surat'] ?? false;
        
        // Check threshold overlap
        $this->validateNoOverlap($jenisPelanggaranId, $validated['frequency_min'], $validated['frequency_max']);
        
        // Auto-assign display_order if not provided
        if (!isset($validated['display_order'])) {
            $maxOrder = PelanggaranFrequencyRule::where('jenis_pelanggaran_id', $jenisPelanggaranId)
                ->max('display_order');
            $validated['display_order'] = ($maxOrder ?? 0) + 1;
        }
        
        $validated['jenis_pelanggaran_id'] = $jenisPelanggaranId;
        
        $rule = PelanggaranFrequencyRule::create($validated);
        
        // Auto-enable frequency rules and activate pelanggaran when first rule is added
        $jenisPelanggaran->update([
            'has_frequency_rules' => true,
            'is_active' => true
        ]);
        
        return redirect()
            ->route('frequency-rules.show', $jenisPelanggaranId)
            ->with('success', 'Frequency rule berhasil ditambahkan');
    }

    /**
     * Update existing frequency rule.
     */
    public function update(Request $request, $ruleId)
    {
        $rule = PelanggaranFrequencyRule::findOrFail($ruleId);
        
        $validated = $request->validate([
            'frequency_min' => 'nullable|integer|min:1',
            'frequency_max' => 'nullable|integer|gte:frequency_min',
            'poin' => 'required|integer|min:0',
            'sanksi_description' => 'required|string|max:500',
            'trigger_surat' => 'nullable|boolean',
            'pembina_roles' => 'required|array|min:1',
            'pembina_roles.*' => 'string',
            'display_order' => 'nullable|integer|min:1',
        ]);
        
        // Default values
        $validated['frequency_min'] = $validated['frequency_min'] ?? 1;
        $validated['trigger_surat'] = $validated['trigger_surat'] ?? false;
        
        // Check threshold overlap (exclude current rule)
        $this->validateNoOverlap(
            $rule->jenis_pelanggaran_id, 
            $validated['frequency_min'], 
            $validated['frequency_max'],
            $ruleId
        );
        
        $rule->update($validated);
        
        return redirect()
            ->route('frequency-rules.show', $rule->jenis_pelanggaran_id)
            ->with('success', 'Frequency rule berhasil diupdate');
    }

    /**
     * Delete frequency rule.
     */
    public function destroy($ruleId)
    {
        $rule = PelanggaranFrequencyRule::findOrFail($ruleId);
        $jenisPelanggaranId = $rule->jenis_pelanggaran_id;
        
        $rule->delete();
        
        // Auto-disable frequency rules and deactivate pelanggaran if no more rules exist
        $remainingRules = PelanggaranFrequencyRule::where('jenis_pelanggaran_id', $jenisPelanggaranId)->count();
        if ($remainingRules == 0) {
            JenisPelanggaran::find($jenisPelanggaranId)->update([
                'has_frequency_rules' => false,
                'is_active' => false
            ]);
        }
        
        return redirect()
            ->route('frequency-rules.show', $jenisPelanggaranId)
            ->with('success', 'Frequency rule berhasil dihapus');
    }

    /**
     * Validate that new/updated threshold doesn't overlap with existing rules.
     */
    protected function validateNoOverlap($jenisPelanggaranId, $freqMin, $freqMax, $excludeRuleId = null)
    {
        $query = PelanggaranFrequencyRule::where('jenis_pelanggaran_id', $jenisPelanggaranId);
        
        if ($excludeRuleId) {
            $query->where('id', '!=', $excludeRuleId);
        }
        
        $existingRules = $query->get();
        
        foreach ($existingRules as $existing) {
            $existingMin = $existing->frequency_min;
            $existingMax = $existing->frequency_max ?? PHP_INT_MAX;
            $newMax = $freqMax ?? PHP_INT_MAX;
            
            // Check if ranges overlap
            if ($freqMin <= $existingMax && $newMax >= $existingMin) {
                $rangeText = $existing->frequency_max 
                    ? "{$existingMin}-{$existing->frequency_max}" 
                    : "{$existingMin}+";
                    
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'frequency_min' => "Threshold overlap dengan rule existing (range: {$rangeText})"
                ]);
            }
        }
    }
}


