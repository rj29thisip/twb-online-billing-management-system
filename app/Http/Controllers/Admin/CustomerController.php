<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\District;
use App\Models\Meter;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CustomerController extends Controller
{
    // ─── Islands of Tonga ──────────────────────────────────────────────────────
    public const ISLANDS = [
        'Tongatapu' => 'TBU',
        'Vava\'u'   => 'VAV',
        'Ha\'apai'  => 'HAP',
        '\'Eua'     => 'EUA',
        'Niuas'     => 'NIU',
    ];

    public function index(Request $request)
    {
        $user = auth()->user();

        $base = $user->isAdmin() || $user->isHeadquarters() || !$user->district_id
            ? Customer::query()
            : Customer::where(function ($q) use ($user) {
                $q->where('district_id', $user->district_id)->orWhereNull('district_id');
              });

        $customers = $base->with(['activeMeter', 'district'])
            ->when($request->search, fn ($q) => $q->where(fn ($q) =>
                $q->where('name', 'like', '%'.$request->search.'%')
                  ->orWhere('given_name', 'like', '%'.$request->search.'%')
                  ->orWhere('family_name', 'like', '%'.$request->search.'%')
                  ->orWhere('account_number', 'like', '%'.$request->search.'%')
                  ->orWhere('phone', 'like', '%'.$request->search.'%')
            ))
            ->when($request->block,       fn ($q) => $q->where('block_number', $request->block))
            ->when($request->status,      fn ($q) => $q->where('status', $request->status))
            ->when($request->island,      fn ($q) => $q->where('island', $request->island))
            ->when($request->district_id && ($user->isAdmin() || $user->isHeadquarters()),
                   fn ($q) => $q->where('district_id', $request->district_id))
            ->orderBy('family_name')->orderBy('given_name')
            ->paginate(20)->withQueryString();

        $blocks    = Customer::distinct()->whereNotNull('block_number')->orderBy('block_number')->pluck('block_number');
        $districts = District::where('is_active', true)->orderByDesc('is_headquarters')->orderBy('name')->get();

        return view('admin.customers.index', compact('customers', 'blocks', 'districts'));
    }

    public function create()
    {
        $districts = District::where('is_active', true)->orderByDesc('is_headquarters')->orderBy('name')->get();
        $defaultDistrictId = auth()->user()->district_id;
        $islands = self::ISLANDS;
        return view('admin.customers.form', compact('districts', 'defaultDistrictId', 'islands'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            // Personal
            'given_name'        => 'required|string|max:100',
            'family_name'       => 'required|string|max:100',
            'date_of_birth'     => 'nullable|date|before:today',
            'gender'            => 'nullable|in:male,female,other,prefer_not_to_say',
            // Account
            'account_number'    => 'required|string|max:20|unique:customers',
            'block_number'      => 'nullable|string|max:20',
            'status'            => 'in:active,inactive,suspended',
            'customer_type'     => 'in:residential,commercial',
            'district_id'       => 'nullable|exists:districts,id',
            // Contact
            'email'             => 'nullable|email|unique:users,email',
            'phone'             => 'nullable|string|max:30',
            // Address
            'address_line'      => 'nullable|string|max:255',
            'suburb'            => 'nullable|string|max:100',
            'island'            => 'nullable|string|max:100',
            'island_code'       => 'nullable|string|max:10',
            // Property
            'deed_number'       => 'nullable|string|max:100',
            'surveyed_date'     => 'nullable|date',
            'property_notes'    => 'nullable|string',
            // Meter
            'meter_id'          => 'nullable|string|unique:meters',
            'endpoint_id'       => 'nullable|string|unique:meters',
            'serial_number'     => 'nullable|string|max:100',
            'meter_type'        => 'nullable|in:residential,commercial',
            'brand'             => 'nullable|string|max:100',
            'model'             => 'nullable|string|max:100',
            'manufacturer'      => 'nullable|string|max:100',
            'installation_date' => 'nullable|date',
            'meter_notes'       => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated, $request) {
            $fullName = trim(($validated['given_name'] ?? '').' '.($validated['family_name'] ?? ''));

            // Derive island_code from island name if not explicitly set
            $islandCode = $validated['island_code'] ?? (self::ISLANDS[$validated['island'] ?? ''] ?? null);

            $customer = Customer::create([
                'name'           => $fullName,
                'given_name'     => $validated['given_name'],
                'family_name'    => $validated['family_name'],
                'date_of_birth'  => $validated['date_of_birth'] ?? null,
                'gender'         => $validated['gender'] ?? null,
                'account_number' => $validated['account_number'],
                'block_number'   => $validated['block_number'] ?? null,
                'status'         => $validated['status'] ?? 'active',
                'customer_type'  => $validated['customer_type'] ?? 'residential',
                'district_id'    => $validated['district_id'] ?? null,
                'email'          => $validated['email'] ?? null,
                'phone'          => $validated['phone'] ?? null,
                'address_line'   => $validated['address_line'] ?? null,
                'suburb'         => $validated['suburb'] ?? null,
                'island'         => $validated['island'] ?? null,
                'island_code'    => $islandCode,
                'address'        => implode(', ', array_filter([
                    $validated['address_line'] ?? null,
                    $validated['suburb'] ?? null,
                    isset($validated['island']) ? ($validated['island'].($islandCode ? ' ('.$islandCode.')' : '')) : null,
                ])),
                'deed_number'    => $validated['deed_number'] ?? null,
                'surveyed_date'  => $validated['surveyed_date'] ?? null,
                'property_notes' => $validated['property_notes'] ?? null,
                'created_by'     => auth()->id(),
            ]);

            AuditLogger::created($customer, [
                'account_number' => $customer->account_number,
                'name'           => $customer->name,
            ]);

            // Portal access
            if (!empty($validated['email'])) {
                $tempPassword = Str::random(10);
                User::create([
                    'name'                 => $fullName,
                    'email'                => $validated['email'],
                    'password'             => bcrypt($tempPassword),
                    'role'                 => 'customer',
                    'customer_id'          => $customer->id,
                    'is_active'            => true,
                    'must_change_password' => true,
                ]);
            }

            // Meter
            if (!empty($validated['meter_id'])) {
                $meter = Meter::create([
                    'customer_id'       => $customer->id,
                    'meter_id'          => $validated['meter_id'],
                    'endpoint_id'       => $validated['endpoint_id'] ?? null,
                    'serial_number'     => $validated['serial_number'] ?? null,
                    'meter_type'        => $validated['meter_type'] ?? 'residential',
                    'brand'             => $validated['brand'] ?? null,
                    'model'             => $validated['model'] ?? null,
                    'manufacturer'      => $validated['manufacturer'] ?? null,
                    'installation_date' => $validated['installation_date'] ?? null,
                    'notes'             => $validated['meter_notes'] ?? null,
                    'status'            => 'active',
                ]);
                AuditLogger::created($meter, ['meter_id' => $meter->meter_id]);
            }
        });

        return redirect()->route('admin.customers.index')
            ->with('success', 'Customer created successfully.');
    }


    /** Deny access if area-scoped user tries to access another district's customer. */
    private function authorizeDistrictAccess(Customer $customer): void
    {
        $user = auth()->user();
        if ($user->isAdmin() || $user->isHeadquarters() || !$user->district_id) return;
        if ($customer->district_id !== null && $customer->district_id !== $user->district_id) {
            abort(403, 'You do not have access to this customer.');
        }
    }

    public function show(Customer $customer)
    {
        $this->authorizeDistrictAccess($customer);
        $customer->load([
            'district', 'createdBy',
            'meters',
            'invoices'  => fn ($q) => $q->limit(12),
            'payments'  => fn ($q) => $q->limit(10)->orderByDesc('payment_date'),
        ]);
        return view('admin.customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        $this->authorizeDistrictAccess($customer);
        $districts         = District::where('is_active', true)->orderByDesc('is_headquarters')->orderBy('name')->get();
        $defaultDistrictId = auth()->user()->district_id;
        $islands           = self::ISLANDS;
        return view('admin.customers.form', compact('customer', 'districts', 'defaultDistrictId', 'islands'));
    }

    public function update(Request $request, Customer $customer)
    {
        $this->authorizeDistrictAccess($customer);
        $validated = $request->validate([
            'given_name'     => 'required|string|max:100',
            'family_name'    => 'required|string|max:100',
            'date_of_birth'  => 'nullable|date|before:today',
            'gender'         => 'nullable|in:male,female,other,prefer_not_to_say',
            'email'          => 'nullable|email',
            'phone'          => 'nullable|string|max:30',
            'block_number'   => 'nullable|string|max:20',
            'status'         => 'in:active,inactive,suspended',
            'customer_type'  => 'in:residential,commercial',
            'district_id'    => 'nullable|exists:districts,id',
            'address_line'   => 'nullable|string|max:255',
            'suburb'         => 'nullable|string|max:100',
            'island'         => 'nullable|string|max:100',
            'island_code'    => 'nullable|string|max:10',
            'deed_number'    => 'nullable|string|max:100',
            'surveyed_date'  => 'nullable|date',
            'property_notes' => 'nullable|string',
        ]);

        $old      = $customer->only(array_keys($validated));
        $fullName = trim(($validated['given_name'] ?? '').' '.($validated['family_name'] ?? ''));
        $islandCode = $validated['island_code'] ?? (self::ISLANDS[$validated['island'] ?? ''] ?? null);

        $customer->update(array_merge($validated, [
            'name'        => $fullName,
            'island_code' => $islandCode,
            'address'     => implode(', ', array_filter([
                $validated['address_line'] ?? null,
                $validated['suburb'] ?? null,
                isset($validated['island']) ? ($validated['island'].($islandCode ? ' ('.$islandCode.')' : '')) : null,
            ])),
        ]));

        AuditLogger::log('updated', 'Customer', $customer->id, $old, $validated,
            "Customer {$customer->account_number} updated");

        return redirect()->route('admin.customers.show', $customer)
            ->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer)
    {
        $this->authorizeDistrictAccess($customer);
        AuditLogger::log('deactivated', 'Customer', $customer->id,
            ['status' => $customer->status], ['status' => 'inactive'],
            "Customer {$customer->account_number} deactivated");
        $customer->update(['status' => 'inactive']);
        return redirect()->route('admin.customers.index')->with('success', 'Customer deactivated.');
    }
}
