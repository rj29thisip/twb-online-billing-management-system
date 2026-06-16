<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TariffTier;
use Illuminate\Http\Request;

class TariffController extends Controller
{
    public function index()
    {
        $residential = TariffTier::where('category', 'residential')
            ->orderBy('effective_from')
            ->orderBy('min_units')
            ->get();

        $commercial = TariffTier::where('category', 'commercial')
            ->orderBy('effective_from')
            ->orderBy('min_units')
            ->get();

        return view('admin.config.tariffs', compact('residential', 'commercial'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'category'       => 'required|in:residential,commercial',
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

        return back()
            ->with('success', 'Tariff tier created successfully.')
            ->with('active_tab', $validated['category']);
    }

    public function update(Request $request, TariffTier $tariff)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'category'       => 'required|in:residential,commercial',
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

        return back()
            ->with('success', 'Tariff tier updated successfully.')
            ->with('active_tab', $validated['category']);
    }

    public function destroy(TariffTier $tariff)
    {
        $category = $tariff->category;
        $tariff->delete();

        return back()
            ->with('success', 'Tariff tier deleted.')
            ->with('active_tab', $category);
    }
}
