<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Meter;
use Illuminate\Http\Request;

class MeterController extends Controller
{
    public function index(Request $request)
    {
        $meters = Meter::with('customer')
            ->when($request->search, fn ($q) => $q->where(fn ($q) =>
                $q->where('meter_id', 'like', '%' . $request->search . '%')
                  ->orWhere('endpoint_id', 'like', '%' . $request->search . '%')
                  ->orWhereHas('customer', fn ($q) =>
                      $q->where('name', 'like', '%' . $request->search . '%')
                        ->orWhere('account_number', 'like', '%' . $request->search . '%')
                  )
            ))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->type,   fn ($q) => $q->where('meter_type', $request->type))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.meters.index', compact('meters'));
    }

    public function create()
    {
        $customers = Customer::where('status', 'active')->orderBy('name')->get();
        return view('admin.meters.form', compact('customers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id'       => 'required|exists:customers,id',
            'meter_id'          => 'required|string|max:50|unique:meters',
            'endpoint_id'       => 'required|string|max:50|unique:meters',
            'meter_type'        => 'required|in:residential,commercial,industrial',
            'installation_date' => 'nullable|date',
            'brand'             => 'nullable|string|max:100',
            'model'             => 'nullable|string|max:100',
            'status'            => 'in:active,inactive,replaced,faulty',
        ]);

        Meter::create($validated);

        return redirect()->route('admin.meters.index')
            ->with('success', 'Meter registered successfully.');
    }

    public function show(Meter $meter)
    {
        $meter->load(['customer', 'readings' => fn ($q) => $q->limit(48)]);
        return view('admin.meters.show', compact('meter'));
    }

    public function edit(Meter $meter)
    {
        $customers = Customer::where('status', 'active')->orderBy('name')->get();
        return view('admin.meters.form', compact('meter', 'customers'));
    }

    public function update(Request $request, Meter $meter)
    {
        $validated = $request->validate([
            'meter_type'           => 'required|in:residential,commercial,industrial',
            'installation_date'    => 'nullable|date',
            'last_maintenance_date'=> 'nullable|date',
            'brand'                => 'nullable|string|max:100',
            'model'                => 'nullable|string|max:100',
            'status'               => 'in:active,inactive,replaced,faulty',
        ]);

        $meter->update($validated);

        return redirect()->route('admin.meters.show', $meter)
            ->with('success', 'Meter updated successfully.');
    }

    public function destroy(Meter $meter)
    {
        $meter->update(['status' => 'inactive']);

        return redirect()->route('admin.meters.index')
            ->with('success', 'Meter deactivated.');
    }
}
