<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TariffTier;
use Illuminate\Http\Request;

class TariffController extends Controller
{
    public function index()
    {
        $tiers = TariffTier::orderBy('min_units')->get();
        return view('admin.config.tariffs', compact('tiers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'min_units'      => 'required|numeric|min:0',
            'max_units'      => 'nullable|numeric|gt:min_units',
            'rate_per_unit'  => 'required|numeric|min:0',
            'unit_type'      => 'required|in:liter,cubicmeter',
            'is_active'      => 'nullable|boolean',
            'effective_from' => 'required|date',
            'effective_to'   => 'nullable|date|after:effective_from',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        TariffTier::create($validated);

        return back()->with('success', 'Tariff tier created successfully.');
    }

    public function update(Request $request, TariffTier $tariff)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'min_units'      => 'required|numeric|min:0',
            'max_units'      => 'nullable|numeric',
            'rate_per_unit'  => 'required|numeric|min:0',
            'unit_type'      => 'required|in:liter,cubicmeter',
            'is_active'      => 'nullable|boolean',
            'effective_from' => 'required|date',
            'effective_to'   => 'nullable|date',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $tariff->update($validated);

        return back()->with('success', 'Tariff tier updated successfully.');
    }

    public function destroy(TariffTier $tariff)
    {
        $tariff->delete();
        return back()->with('success', 'Tariff tier deleted.');
    }
}
