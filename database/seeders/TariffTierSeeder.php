<?php

namespace Database\Seeders;

use App\Models\TariffTier;
use Illuminate\Database\Seeder;

class TariffTierSeeder extends Seeder
{
    public function run(): void
    {
        // Detach existing invoice_items references first (preserve invoice history,
        // but the old tier rows are being replaced)
        \DB::table('invoice_items')->whereNotNull('tariff_tier_id')->update(['tariff_tier_id' => null]);

        // Clear existing tiers
        TariffTier::query()->delete();

        $effectiveFrom = '2025-01-01';

        $tiers = [
            // ── Residential ──────────────────────────────────────────────────────
            [
                'name'           => 'Residential — Tier 1 (0–100 m³)',
                'category'       => 'residential',
                'min_units'      => 0,
                'max_units'      => 100,
                'rate_per_unit'  => 2.00,
                'unit_type'      => 'cubicmeter',
                'is_active'      => true,
                'effective_from' => $effectiveFrom,
                'effective_to'   => null,
            ],
            [
                'name'           => 'Residential — Tier 2 (100–300 m³)',
                'category'       => 'residential',
                'min_units'      => 100,
                'max_units'      => 300,
                'rate_per_unit'  => 3.00,
                'unit_type'      => 'cubicmeter',
                'is_active'      => true,
                'effective_from' => $effectiveFrom,
                'effective_to'   => null,
            ],
            [
                'name'           => 'Residential — Tier 3 (300+ m³)',
                'category'       => 'residential',
                'min_units'      => 300,
                'max_units'      => null,
                'rate_per_unit'  => 4.00,
                'unit_type'      => 'cubicmeter',
                'is_active'      => true,
                'effective_from' => $effectiveFrom,
                'effective_to'   => null,
            ],

            // ── Commercial ───────────────────────────────────────────────────────
            [
                'name'           => 'Commercial — Tier 1 (0–100 m³)',
                'category'       => 'commercial',
                'min_units'      => 0,
                'max_units'      => 100,
                'rate_per_unit'  => 4.00,
                'unit_type'      => 'cubicmeter',
                'is_active'      => true,
                'effective_from' => $effectiveFrom,
                'effective_to'   => null,
            ],
            [
                'name'           => 'Commercial — Tier 2 (100–300 m³)',
                'category'       => 'commercial',
                'min_units'      => 100,
                'max_units'      => 300,
                'rate_per_unit'  => 5.00,
                'unit_type'      => 'cubicmeter',
                'is_active'      => true,
                'effective_from' => $effectiveFrom,
                'effective_to'   => null,
            ],
            [
                'name'           => 'Commercial — Tier 3 (300+ m³)',
                'category'       => 'commercial',
                'min_units'      => 300,
                'max_units'      => null,
                'rate_per_unit'  => 6.00,
                'unit_type'      => 'cubicmeter',
                'is_active'      => true,
                'effective_from' => $effectiveFrom,
                'effective_to'   => null,
            ],
        ];

        foreach ($tiers as $tier) {
            TariffTier::create($tier);
        }
    }
}
