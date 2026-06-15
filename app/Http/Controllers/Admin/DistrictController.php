<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\District;
use App\Services\AuditLogger;
use Illuminate\Http\Request;

class DistrictController extends Controller
{
    public function index()
    {
        $districts = District::withCount(['users', 'customers'])
            ->orderByDesc('is_headquarters')->orderBy('name')->paginate(15);
        return view('admin.districts.index', compact('districts'));
    }

    public function create() { return view('admin.districts.create'); }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:100',
            'code'            => 'required|string|max:20|unique:districts,code',
            'is_headquarters' => 'boolean',
            'description'     => 'nullable|string|max:500',
        ]);
        $data['code']      = strtoupper($data['code']);
        $data['is_active'] = true;

        if (!empty($data['is_headquarters'])) {
            District::where('is_headquarters', true)->update(['is_headquarters' => false]);
        }

        $district = District::create($data);

        AuditLogger::log('district_created', 'District', $district->id, [], [
            'name' => $district->name,
            'code' => $district->code,
            'is_headquarters' => $district->is_headquarters,
        ], "District created: {$district->name} ({$district->code})");

        return redirect()->route('admin.districts.index')
            ->with('success', "District '{$district->name}' created.");
    }

    public function edit(District $district)
    {
        return view('admin.districts.edit', compact('district'));
    }

    public function update(Request $request, District $district)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:100',
            'code'            => 'required|string|max:20|unique:districts,code,'.$district->id,
            'is_headquarters' => 'boolean',
            'description'     => 'nullable|string|max:500',
        ]);
        $data['code'] = strtoupper($data['code']);

        if (!empty($data['is_headquarters']) && !$district->is_headquarters) {
            District::where('is_headquarters', true)->update(['is_headquarters' => false]);
        }

        $old = $district->only(['name', 'code', 'is_headquarters']);
        $district->update($data);

        AuditLogger::log('district_updated', 'District', $district->id, $old, [
            'name' => $district->name,
            'code' => $district->code,
        ], "District updated: {$district->name} ({$district->code})");

        return redirect()->route('admin.districts.index')
            ->with('success', "District '{$district->name}' updated.");
    }

    public function toggleActive(Request $request, District $district)
    {
        if ($district->is_headquarters && $district->is_active) {
            return back()->withErrors(['error' => 'The Headquarters district cannot be deactivated.']);
        }

        $newState = !$district->is_active;
        $state    = $newState ? 'activated' : 'deactivated';
        $district->update(['is_active' => $newState]);

        AuditLogger::log("district_{$state}", 'District', $district->id,
            ['is_active' => !$newState],
            ['is_active' => $newState],
            "District '{$district->name}' was {$state}."
        );

        return back()->with('success', "District '{$district->name}' has been {$state}.");
    }
}
