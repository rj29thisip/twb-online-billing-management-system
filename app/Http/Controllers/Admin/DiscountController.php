<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Discount;
use Illuminate\Http\Request;

class DiscountController extends Controller
{
    public function index()
    {
        $discounts = Discount::orderByDesc('created_at')->get();
        return view('admin.config.discounts', compact('discounts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'discount_type' => 'required|in:percent,fixed',
            'value'         => 'required|numeric|min:0',
            'applies_to'    => 'required|in:all,individual',
            'is_active'     => 'nullable|boolean',
        ]);
        $validated['is_active'] = $request->boolean('is_active');
        Discount::create($validated);
        return back()->with('success', 'Discount created.');
    }

    public function update(Request $request, Discount $discount)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'discount_type' => 'required|in:percent,fixed',
            'value'         => 'required|numeric|min:0',
            'applies_to'    => 'required|in:all,individual',
            'is_active'     => 'nullable|boolean',
        ]);
        $validated['is_active'] = $request->boolean('is_active');
        $discount->update($validated);
        return back()->with('success', 'Discount updated.');
    }

    public function destroy(Discount $discount)
    {
        $discount->delete();
        return back()->with('success', 'Discount deleted.');
    }
}
