<?php
// ── TaxController ─────────────────────────────────────────────────────────────
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TaxRate;
use Illuminate\Http\Request;

class TaxController extends Controller
{
    public function index()
    {
        $taxes = TaxRate::orderByDesc('effective_from')->get();
        return view('admin.config.taxes', compact('taxes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:100',
            'rate_percent'   => 'required|numeric|min:0|max:100',
            'is_active'      => 'nullable|boolean',
            'effective_from' => 'required|date',
        ]);
        $validated['is_active'] = $request->boolean('is_active');
        TaxRate::create($validated);
        return back()->with('success', 'Tax rate created.');
    }

    public function update(Request $request, TaxRate $tax)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:100',
            'rate_percent'   => 'required|numeric|min:0|max:100',
            'is_active'      => 'nullable|boolean',
            'effective_from' => 'required|date',
        ]);
        $validated['is_active'] = $request->boolean('is_active');
        $tax->update($validated);
        return back()->with('success', 'Tax rate updated.');
    }

    public function destroy(TaxRate $tax)
    {
        $tax->delete();
        return back()->with('success', 'Tax rate deleted.');
    }
}
