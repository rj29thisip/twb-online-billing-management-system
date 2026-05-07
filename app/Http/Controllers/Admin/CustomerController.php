<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Meter;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $customers = Customer::with('activeMeter')
            ->when($request->search, fn ($q) => $q->where(fn ($q) =>
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('account_number', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%')
            ))
            ->when($request->block,  fn ($q) => $q->where('block_number', $request->block))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $blocks = Customer::select('block_number')
            ->distinct()
            ->whereNotNull('block_number')
            ->orderBy('block_number')
            ->pluck('block_number');

        return view('admin.customers.index', compact('customers', 'blocks'));
    }

    public function create()
    {
        return view('admin.customers.form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'account_number'    => 'required|string|max:20|unique:customers',
            'email'             => 'nullable|email|unique:users,email',
            'phone'             => 'nullable|string|max:20',
            'block_number'      => 'nullable|string|max:20',
            'address'           => 'nullable|string',
            'status'            => 'in:active,inactive,suspended',
            'meter_id'          => 'nullable|string|unique:meters',
            'endpoint_id'       => 'nullable|string|unique:meters',
            'meter_type'        => 'nullable|in:residential,commercial,industrial',
            'installation_date' => 'nullable|date',
        ]);

        DB::transaction(function () use ($validated) {
            $customer = Customer::create([
                'name'           => $validated['name'],
                'account_number' => $validated['account_number'],
                'email'          => $validated['email'] ?? null,
                'phone'          => $validated['phone'] ?? null,
                'block_number'   => $validated['block_number'] ?? null,
                'address'        => $validated['address'] ?? null,
                'status'         => $validated['status'] ?? 'active',
            ]);

            // Audit
            AuditLogger::created($customer, [
                'account_number' => $customer->account_number,
                'name'           => $customer->name,
                'block_number'   => $customer->block_number,
            ]);

            if (! empty($validated['email'])) {
                $tempPassword = Str::random(10);
                User::create([
                    'name'        => $validated['name'],
                    'email'       => $validated['email'],
                    'password'    => bcrypt($tempPassword),
                    'role'        => 'customer',
                    'customer_id' => $customer->id,
                    'is_active'   => true,
                ]);
            }

            if (! empty($validated['meter_id'])) {
                $meter = Meter::create([
                    'customer_id'       => $customer->id,
                    'meter_id'          => $validated['meter_id'],
                    'endpoint_id'       => $validated['endpoint_id'] ?? null,
                    'meter_type'        => $validated['meter_type'] ?? 'residential',
                    'installation_date' => $validated['installation_date'] ?? null,
                    'status'            => 'active',
                ]);
                AuditLogger::created($meter, ['meter_id' => $meter->meter_id, 'customer' => $customer->account_number]);
            }
        });

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer created successfully.');
    }

    public function show(Customer $customer)
    {
        $customer->load([
            'meters',
            'invoices' => fn ($q) => $q->limit(12),
            'payments' => fn ($q) => $q->limit(10)->orderByDesc('payment_date'),
        ]);

        return view('admin.customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        return view('admin.customers.form', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'nullable|email',
            'phone'        => 'nullable|string|max:20',
            'block_number' => 'nullable|string|max:20',
            'address'      => 'nullable|string',
            'status'       => 'in:active,inactive,suspended',
        ]);

        $old = $customer->only(array_keys($validated));
        $customer->update($validated);

        AuditLogger::log('updated', 'Customer', $customer->id, $old, $validated,
            "Customer {$customer->account_number} updated");

        return redirect()->route('admin.customers.show', $customer)
            ->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer)
    {
        AuditLogger::log('deactivated', 'Customer', $customer->id,
            ['status' => $customer->status], ['status' => 'inactive'],
            "Customer {$customer->account_number} deactivated");

        $customer->update(['status' => 'inactive']);

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer deactivated.');
    }
}
