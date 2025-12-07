<?php

namespace App\Http\Controllers;

use App\Models\PembinaanInternalRule;
use Illuminate\Http\Request;

class PembinaanInternalRulesController extends Controller
{
    /**
     * Display list semua pembinaan internal rules.
     */
    public function index()
    {
        $rules = PembinaanInternalRule::orderBy('display_order')->get();
        
        return view('pembinaan-internal-rules.index', compact('rules'));
    }

    /**
     * Store new pembinaan internal rule.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'poin_min' => 'required|integer|min:0',
            'poin_max' => 'nullable|integer|gte:poin_min',
            'pembina_roles' => 'required|array|min:1',
            'pembina_roles.*' => 'string',
            'keterangan' => 'required|string|max:500',
            'display_order' => 'nullable|integer|min:1',
        ]);
        
        // Check range overlap
        $this->validateNoOverlap($validated['poin_min'], $validated['poin_max']);
        
        // Auto-assign display_order if not provided
        if (!isset($validated['display_order'])) {
            $maxOrder = PembinaanInternalRule::max('display_order');
            $validated['display_order'] = ($maxOrder ?? 0) + 1;
        }
        
        PembinaanInternalRule::create($validated);
        
        return redirect()
            ->route('pembinaan-internal-rules.index')
            ->with('success', 'Aturan pembinaan internal berhasil ditambahkan');
    }

    /**
     * Update existing pembinaan internal rule.
     */
    public function update(Request $request, $id)
    {
        $rule = PembinaanInternalRule::findOrFail($id);
        
        $validated = $request->validate([
            'poin_min' => 'required|integer|min:0',
            'poin_max' => 'nullable|integer|gte:poin_min',
            'pembina_roles' => 'required|array|min:1',
            'pembina_roles.*' => 'string',
            'keterangan' => 'required|string|max:500',
            'display_order' => 'nullable|integer|min:1',
        ]);
        
        // Check range overlap (exclude current rule)
        $this->validateNoOverlap($validated['poin_min'], $validated['poin_max'], $id);
        
        $rule->update($validated);
        
        return redirect()
            ->route('pembinaan-internal-rules.index')
            ->with('success', 'Aturan pembinaan internal berhasil diupdate');
    }

    /**
     * Delete pembinaan internal rule.
     */
    public function destroy($id)
    {
        $rule = PembinaanInternalRule::findOrFail($id);
        $rule->delete();
        
        return redirect()
            ->route('pembinaan-internal-rules.index')
            ->with('success', 'Aturan pembinaan internal berhasil dihapus');
    }

    /**
     * Validate that new/updated range doesn't overlap with existing rules.
     */
    protected function validateNoOverlap($poinMin, $poinMax, $excludeRuleId = null)
    {
        $query = PembinaanInternalRule::query();
        
        if ($excludeRuleId) {
            $query->where('id', '!=', $excludeRuleId);
        }
        
        $existingRules = $query->get();
        
        foreach ($existingRules as $existing) {
            $existingMin = $existing->poin_min;
            $existingMax = $existing->poin_max ?? PHP_INT_MAX;
            $newMax = $poinMax ?? PHP_INT_MAX;
            
            // Check if ranges overlap
            if ($poinMin <= $existingMax && $newMax >= $existingMin) {
                $rangeText = $existing->poin_max 
                    ? "{$existingMin}-{$existing->poin_max}" 
                    : "{$existingMin}+";
                    
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'poin_min' => "Range overlap dengan aturan existing (range: {$rangeText} poin)"
                ]);
            }
        }
    }
}
