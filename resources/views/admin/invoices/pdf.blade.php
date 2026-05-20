<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Invoice {{ $invoice->invoice_number }} — Tonga Water Board</title>
  <style>
    @page { size: A4; margin: 0; }

    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: Arial, Helvetica, sans-serif;
      font-size: 11px;
      color: #000;
      background: #fff;
      padding: 0;
    }

    /* ── NO-PRINT TOOLBAR ──────────────────────────────── */
    .no-print {
      background: #1a2035;
      padding: 10px 20px;
      display: flex;
      align-items: center;
      gap: 12px;
    }
    .no-print button {
      padding: 8px 20px;
      background: #1a73e8;
      color: #fff;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 13px;
      font-weight: 600;
    }
    .no-print a { color: #90caf9; font-size: 13px; text-decoration: none; }

    /* ── PAGE WRAPPER ──────────────────────────────────── */
    .page {
      width: 210mm;
      min-height: 297mm;
      margin: 0 auto;
      padding: 8mm 10mm;
      background: #fff;
      page-break-after: avoid;
      page-break-inside: avoid;
      position: relative;
    }

    /* ── Ensure content doesn't force extra page ───────── */
    .page-content {
      height: auto;
      overflow: visible;
    }

    /* ── HEADER ────────────────────────────────────────── */
    .header {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      border-bottom: 2px solid #000;
      padding-bottom: 6px;
      margin-bottom: 6px;
    }

    .logo-area {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .logo-circle {
      width: 60px;
      height: 60px;
      border-radius: 50%;
      border: 3px solid #000;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 7px;
      font-weight: 700;
      text-align: center;
      line-height: 1.3;
      flex-shrink: 0;
      position: relative;
      overflow: hidden;
    }

    .logo-inner {
      font-size: 9px;
      font-weight: 900;
      text-align: center;
      line-height: 1.2;
      color: #000;
    }

    .logo-sub { font-size: 7px; letter-spacing: 1px; }

    .header-title {
      flex: 1;
      text-align: center;
    }

    .header-title h1 {
      font-size: 22px;
      font-weight: 900;
      letter-spacing: 2px;
      text-transform: uppercase;
    }

    .header-title .po-box {
      font-size: 10px;
      margin-top: 2px;
    }

    .doc-title-bar {
      background: #000;
      color: #fff;
      text-align: center;
      font-size: 12px;
      font-weight: 700;
      padding: 3px 10px;
      margin-top: 4px;
      letter-spacing: 0.5px;
    }

    /* ── TWO-COLUMN MAIN LAYOUT ────────────────────────── */
    .main-layout {
      display: flex;
      gap: 8px;
      margin-top: 8px;
    }

    .col-left  { width: 42%; }
    .col-right { width: 58%; }

    /* ── CUSTOMER INFO BOX ─────────────────────────────── */
    .customer-box {
      border: 1px solid #000;
      padding: 6px 8px;
      margin-bottom: 8px;
      min-height: 60px;
    }

    .customer-name { font-weight: 700; font-size: 12px; margin-bottom: 2px; }
    .customer-addr { font-size: 10px; line-height: 1.5; }

    /* ── CHART SECTION ─────────────────────────────────── */
    .chart-section {
      border: 1px solid #000;
      padding: 4px 6px;
      margin-bottom: 8px;
    }

    .chart-title {
      font-size: 9px;
      font-weight: 700;
      margin-bottom: 4px;
      text-align: center;
    }

    .bar-chart {
      display: flex;
      align-items: flex-end;
      gap: 3px;
      height: 70px;
      padding: 0 4px;
      position: relative;
    }

    .chart-y-axis {
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      font-size: 7px;
      color: #333;
      text-align: right;
      padding-right: 3px;
      height: 70px;
      min-width: 22px;
    }

    .bar-wrap {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: flex-end;
      flex: 1;
      height: 70px;
      position: relative;
    }

    .bar {
      width: 100%;
      background: #333;
      min-height: 1px;
    }

    .bar-label {
      font-size: 6px;
      color: #555;
      margin-top: 2px;
      text-align: center;
      white-space: nowrap;
      overflow: hidden;
      max-width: 24px;
    }

    .chart-note { font-size: 8px; color: #444; text-align: center; margin-top: 2px; }

    /* ── HISTORICAL INFO ────────────────────────────────── */
    .hist-box {
      border: 1px solid #000;
      padding: 5px 7px;
      font-size: 9px;
      line-height: 1.7;
    }

    .hist-box .hist-title {
      font-weight: 700;
      font-size: 10px;
      border-bottom: 1px solid #ccc;
      margin-bottom: 4px;
      padding-bottom: 2px;
    }

    /* ── RIGHT COLUMN - ACCOUNT SUMMARY ────────────────── */
    .account-header {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 4px;
      border: 1px solid #000;
      padding: 5px 8px;
      margin-bottom: 6px;
    }

    .account-header .field { font-size: 9px; margin-bottom: 3px; }
    .account-header .field .label { color: #555; }
    .account-header .field .val   { font-weight: 700; font-size: 10px; }

    /* ── CHARGES TABLE ──────────────────────────────────── */
    .charges-section {
      border: 1px solid #000;
      margin-bottom: 6px;
    }

    .charges-section .row {
      display: flex;
      justify-content: space-between;
      padding: 3px 8px;
      border-bottom: 1px solid #ddd;
      font-size: 10px;
    }

    .charges-section .row:last-child { border-bottom: none; }
    .charges-section .row .label     { flex: 1; }
    .charges-section .row .amount    { font-weight: 600; min-width: 60px; text-align: right; }

    .charges-section .row.section-header {
      background: #f0f0f0;
      font-weight: 700;
      font-size: 10px;
    }

    .charges-section .row.total-row {
      background: #000;
      color: #fff;
      font-weight: 700;
      font-size: 12px;
      padding: 5px 8px;
    }

    .charges-section .row.balance-before {
      background: #e8e8e8;
      font-weight: 700;
    }

    /* ── NOTES SECTION ──────────────────────────────────── */
    .notes-section {
      border: 1px solid #000;
      padding: 5px 8px;
      margin-top: 6px;
    }

    .notes-section .notes-title {
      font-weight: 700;
      font-size: 10px;
      margin-bottom: 4px;
    }

    .notes-section ol {
      padding-left: 14px;
      font-size: 8.5px;
      line-height: 1.7;
      color: #111;
    }

    .notes-section ol li { margin-bottom: 4px; }

    /* ── USAGE DETAIL TABLE ─────────────────────────────── */
    .usage-detail {
      margin-top: 8px;
      border: 1px solid #000;
    }

    .usage-detail .ud-title {
      background: #000;
      color: #fff;
      font-weight: 700;
      font-size: 10px;
      padding: 4px 8px;
      text-align: center;
    }

    .usage-detail table {
      width: 100%;
      border-collapse: collapse;
      font-size: 9.5px;
    }

    .usage-detail table thead tr {
      background: #e8e8e8;
    }

    .usage-detail table th {
      padding: 4px 6px;
      border: 1px solid #ccc;
      font-weight: 700;
      text-align: center;
      font-size: 9px;
      line-height: 1.3;
    }

    .usage-detail table td {
      padding: 4px 6px;
      border: 1px solid #ccc;
      text-align: center;
    }

    .usage-detail .ud-total {
      display: flex;
      justify-content: space-between;
      padding: 4px 8px;
      font-weight: 700;
      font-size: 11px;
      border-top: 2px solid #000;
      background: #f5f5f5;
    }

    /* ── TAX NOTE ───────────────────────────────────────── */
    .tax-note {
      font-size: 8.5px;
      margin-top: 8px;
      padding: 4px 6px;
      border: 1px solid #ccc;
      color: #333;
      line-height: 1.6;
    }
    .tax-note strong { 
      font-size: 9px; 
      display: block;
      margin-bottom: 4px;
    }

    /* ── FOOTER STAMP ───────────────────────────────────── */
    .footer-stamp {
      margin-top: 10px;
      font-size: 7px;
      color: #999;
      text-align: left;
    }

    /* ── PRINT ──────────────────────────────────────────── */
    @media print {
      .no-print { display: none !important; }
      body { padding: 0; }
      .page { width: 100%; padding: 8mm 10mm; }
    }
  </style>
</head>
<body>

{{-- ── TOOLBAR ────────────────────────────────────────────── --}}
<div class="no-print">
  <button onclick="window.print()">&#128438; Print / Save as PDF</button>
  @isset($backRoute)
    <a href="{{ $backRoute }}">← Back to Invoice</a>
  @else
    <a href="javascript:history.back()">← Back to Invoice</a>
  @endisset
  <span style="color:#90caf9;margin-left:auto;font-size:12px;">
    Invoice {{ $invoice->invoice_number }} — {{ $invoice->customer->name }}
  </span>
</div>

<div class="page">
  <div class="page-content">

  {{-- ══ HEADER ═══════════════════════════════════════════ --}}
  <div class="header">
    <div class="logo-area">
      <div class="logo-circle">
        <div class="logo-inner">
          <img src="..\"></img><br>WATER<br>BOARD
        </div>
      </div>
    </div>
    <div class="header-title">
      <h1>TONGA WATER BOARD</h1>
      <div class="po-box">PO Box 92, Nuku'alofa, TONGA</div>
    </div>
    <div style="min-width:60px;"></div>
  </div>

  <div class="doc-title-bar">
    Consumption Tax Invoice / Statement — Faka'eke'eke 'o e Mo'ua Vai
  </div>

  {{-- ══ MAIN TWO-COLUMN LAYOUT ════════════════════════════ --}}
  <div class="main-layout">

    {{-- ── LEFT COLUMN ─────────────────────────────────── --}}
    <div class="col-left">

      {{-- Customer --}}
      <div class="customer-box">
        <div class="customer-name">{{ strtoupper($invoice->customer->name) }}</div>
        @if($invoice->customer->address)
          <div class="customer-addr">{{ $invoice->customer->address }}</div>
        @endif
        @if($invoice->customer->block_number)
          <div class="customer-addr">{{ $invoice->customer->block_number }}</div>
        @endif
        @if($invoice->customer->phone)
          <div class="customer-addr">Tel: {{ $invoice->customer->phone }}</div>
        @endif
      </div>

      {{-- Historical Chart --}}
      <div class="chart-section">
        <div class="chart-title">
          Customer Water Supply<br>Previous 6 Readings
        </div>

        @php
          // Get last 6 monthly readings for chart
          $meter         = $invoice->meter;
          $chartReadings = collect();
          if ($meter) {
            $chartReadings = \App\Models\MeterReading::where('meter_id', $meter->id)
              ->where('capture_time', '<', $invoice->billing_period_start)
              ->select(\Illuminate\Support\Facades\DB::raw('DATE_FORMAT(capture_time, "%y-%m-%d") as lbl'),
                       \Illuminate\Support\Facades\DB::raw('SUM(usage) as total'))
              ->groupBy('lbl')
              ->orderByDesc('lbl')
              ->limit(6)
              ->get()
              ->reverse()
              ->values();
          }
          $maxUsage = $chartReadings->max('total') ?: 1;
        @endphp

        <div style="display:flex;align-items:flex-end;gap:2px;">
          {{-- Y-axis labels --}}
          <div class="chart-y-axis">
            <span>{{ number_format($maxUsage/1000,0) }}</span>
            <span>{{ number_format($maxUsage/1000*0.75,0) }}</span>
            <span>{{ number_format($maxUsage/1000*0.5,0) }}</span>
            <span>{{ number_format($maxUsage/1000*0.25,0) }}</span>
            <span>0</span>
          </div>
          {{-- Bars --}}
          <div style="display:flex;align-items:flex-end;gap:3px;flex:1;height:70px;border-left:1px solid #888;border-bottom:1px solid #888;">
            @forelse($chartReadings as $cr)
              @php $barHeight = max(1, round(($cr->total / $maxUsage) * 66)); @endphp
              <div class="bar-wrap">
                <div class="bar" style="height:{{ $barHeight }}px;"></div>
                <div class="bar-label">{{ substr($cr->lbl, 3, 5) }}</div>
              </div>
            @empty
              <div class="bar-wrap">
                <div class="bar" style="height:1px;"></div>
                <div class="bar-label">—</div>
              </div>
            @endforelse
            {{-- Current period bar (highlighted) --}}
            @php
              $currentUsage = (float)$invoice->total_usage * 1000; // convert m3 to liters
              $currentBarH  = max(1, round(($currentUsage / $maxUsage) * 66));
            @endphp
            <div class="bar-wrap">
              <div class="bar" style="height:{{ $currentBarH }}px;background:#000;"></div>
              <div class="bar-label">{{ \Carbon\Carbon::parse($invoice->billing_period_end)->format('d/m') }}</div>
            </div>
          </div>
        </div>

        <div class="chart-note">Reading Date ('Aho lau)</div>
      </div>

      {{-- Historical Info --}}
      <div class="hist-box">
        <div class="hist-title">
          Historical Water Supply Information<br>
          <span style="font-weight:400;font-size:8px;">(Hisitolia 'oe Vai na'e Tufaki atu)</span>
        </div>

        @php
          $totalHistorical = $chartReadings->sum('total');
          $avgMonthly      = $chartReadings->count() > 0 ? round($totalHistorical / $chartReadings->count()) : 0;
          $currentLiters   = round((float)$invoice->total_usage * 1000);
        @endphp

        <div>Previous 6 Readings Dates Average</div>
        <div>Water Supplied - {{ number_format($totalHistorical) }} litres</div>
        <div>'Avalisi 'oe Vai na'e Tufaki atu he fo'i 'aho lau 'e 6 ki mu'a - {{ number_format($totalHistorical) }} lita</div>
        <br>
        <div>Current Water Supplied - {{ number_format($currentLiters) }} litres</div>
        <div>Vai na'e Tufaki atu he mahina ni - {{ number_format($currentLiters) }} lita</div>
        <br>
        <div>Average Water Supplied - {{ number_format($avgMonthly) }} litres</div>
        <div>Faka'avalisi 'oe Vai na'e Tufaki atu 'ihe 'aho - {{ number_format($avgMonthly) }} lita</div>
      </div>

    </div>

    {{-- ── RIGHT COLUMN ────────────────────────────────── --}}
    <div class="col-right">

      {{-- Account Header --}}
      <div class="account-header">
        <div class="field">
          <div class="label">Customer Account:</div>
          <div class="val">{{ $invoice->customer->account_number }}</div>
        </div>
        <div class="field">
          <div class="label">Invoice Date:</div>
          <div class="val">{{ $invoice->issued_at?->format('d M y') ?? now()->format('d M y') }}</div>
        </div>
        <div class="field">
          <div class="label">For Water Supplied From / Ki he vai na'e tufaki mei he:</div>
          <div class="val">{{ $invoice->billing_period_start->format('d/m/y') }} to / ki he {{ $invoice->billing_period_end->format('d/m/y') }}</div>
        </div>
        <div>
          <div class="field">
            <div class="label">Account Enquiries / Faka'eke'eke mo'ua:</div>
            <div class="val">23299</div>
          </div>
          <div class="field"><div class="label">Office:</div><div class="val">23518</div></div>
          <div class="field"><div class="label">Emergencies (After Hours):</div><div class="val">23095</div></div>
          <div class="field"><div class="label">TIN:</div><div class="val">257118</div></div>
        </div>
      </div>

      {{-- Charges Section --}}
      <div class="charges-section">

        {{-- (1) Opening Balance --}}
        <div class="row section-header">
          <div class="label">(1) Opening Balance</div>
        </div>
        @php
          // Previous unpaid balance
          $previousBalance = \App\Models\Invoice::where('customer_id', $invoice->customer_id)
            ->where('id', '<', $invoice->id)
            ->whereIn('status', ['issued','partially_paid','overdue'])
            ->sum('balance_due');
        @endphp
        <div class="row">
          <div class="label">Opening Balance / Palanisi 'oe kamata</div>
          <div class="amount">${{ number_format($previousBalance, 2) }}CF</div>
        </div>
        <div class="row">
          <div class="label">Payment Received / Totongi 'i hokosi</div>
          <div class="amount">${{ number_format($invoice->amount_paid, 2) }}</div>
        </div>
        <div class="row balance-before">
          <div class="label">
            <strong>Balance Before Current Charges / Palanisi kimu'a he Mo'ua lolotonga</strong>
          </div>
          <div class="amount" style="font-weight:700;">
            ${{ number_format($previousBalance, 2) }}CI
          </div>
        </div>

        {{-- (2) Current Charges --}}
        <div class="row section-header" style="margin-top:4px;">
          <div class="label">(2) Jobs / Ngaue na'e Fakahoko</div>
        </div>
        <div class="row">
          <div class="label">Reconnection Fees / Totongi Hoko</div>
          <div class="amount">—</div>
        </div>
        <div class="row">
          <div class="label">
            Current month Water Charges (please see <strong>(1)</strong> below for details)<br>
            <span style="font-size:8.5px;">/ Mo'ua vai ki he mahina ni (vakai ki hono fakaikiiki 'i he (1) 'i lalo)</span>
          </div>
          <div class="amount">${{ number_format($invoice->subtotal, 2) }}</div>
        </div>

        @foreach($invoice->items as $item)
          <div class="row" style="padding-left:16px;font-size:9px;color:#444;">
            <div class="label">{{ $item->description }}</div>
            <div class="amount">${{ number_format($item->line_total, 2) }}</div>
          </div>
        @endforeach

        @if($invoice->discount_amount > 0)
          <div class="row">
            <div class="label">Less {{ $invoice->discount_amount > 0 ? round(($invoice->discount_amount / $invoice->subtotal) * 100) . '%' : '' }} discount</div>
            <div class="amount" style="color:#555;">-${{ number_format($invoice->discount_amount, 2) }}</div>
          </div>
        @endif

        <div class="row">
          <div class="label">Consumption Tax {{ number_format($invoice->tax_amount / max(1,$invoice->subtotal) * 100, 0) }}%</div>
          <div class="amount">${{ number_format($invoice->tax_amount, 2) }}</div>
        </div>

        {{-- TOTAL --}}
        <div class="row total-row">
          <div class="label">
            Total Amount Due / Fakakatoa Mo'ua ke totongi
          </div>
          <div class="amount" style="color:#fff;font-size:14px;">
            ${{ number_format($invoice->total_amount, 2) }}
          </div>
        </div>

      </div>

      {{-- Payment Status --}}
      @if($invoice->amount_paid > 0)
        <div style="display:flex;justify-content:space-between;padding:4px 8px;background:#e8f5e9;border:1px solid #4caf50;font-size:10px;margin-bottom:6px;">
          <span style="color:#2e7d32;font-weight:700;">Amount Paid / Totongi 'i hokosi</span>
          <span style="color:#2e7d32;font-weight:700;">${{ number_format($invoice->amount_paid, 2) }}</span>
        </div>
        @if($invoice->balance_due > 0)
          <div style="display:flex;justify-content:space-between;padding:4px 8px;background:#fff3e0;border:1px solid #ff9800;font-size:10px;margin-bottom:6px;">
            <span style="color:#e65100;font-weight:700;">Balance Due / Palanisi ke totongi</span>
            <span style="color:#e65100;font-weight:700;">${{ number_format($invoice->balance_due, 2) }}</span>
          </div>
        @else
          <div style="text-align:center;padding:4px;background:#e8f5e9;border:1px solid #4caf50;font-size:11px;font-weight:700;color:#2e7d32;margin-bottom:6px;">
            ✓ PAID IN FULL / TOTONGI KAKATO
          </div>
        @endif
      @endif

      {{-- Notes --}}
      <div class="notes-section">
        <div class="notes-title">Notes:</div>
        <ol>
          <li>
            If any part of the Past Due Amount remains unpaid your water supply will be disconnected without further notice.<br>
            <em style="color:#444;">Kapau 'oku 'i ai ha toenga mo'ua 'oku te'eki totongi mai 'e tu'usi atu leva ho'o mo'u'anga vai 'ikai toe fanongonongo atu.</em>
          </li>
          <li>
            The Current Water Bill Amount is due for payment within 30 days of bill date {{ $invoice->due_date?->format('d/m/y') ?? '' }}.<br>
            <em style="color:#444;">Koe mo'ua lolotonga ke totongi 'i loto he 'aho 'e 30 mei he 'aho 'oe mo'ua ni.</em>
          </li>
          <li>
            Please check your historical water usage information and also check your internal water supply for any unnecessary increase in water bill and usage.<br>
            <em style="color:#444;">Kataki 'o fakapapau'i 'ae lahi ho'o ngaue'aki vai mei he hisitolia ho'o ngaue'aki vai pea mo e totongi vai pea mo vakai'i ho'o mo'uanga vai 'i loto 'api 'oku malu kapau 'oku ngali lahi 'a ho'o mo'ua mo e ngaue'aki vai.</em>
          </li>
        </ol>
      </div>

    </div>
  </div>

  {{-- ══ USAGE DETAIL TABLE ══════════════════════════════════ --}}
  <div class="usage-detail">
    <div class="ud-title">
      (1) Current Month Water Charges Usage Details / Ko e fakaikiiki 'eni 'o e mo'ua vai ki he mahina ni
    </div>
    <table>
      <thead>
        <tr>
          <th>Item / Fakaikiiki</th>
          <th>Previous Reading<br>/ Lau Faka'osi</th>
          <th>Present Reading<br>/ Mo'ua ko eni</th>
          <th>Units Supplied<br>/ 'Iuniti na'e Tufaki atu</th>
          <th>Rate<br>/ Totongi</th>
          <th>Amount<br>/ Fakakatoa</th>
        </tr>
      </thead>
      <tbody>
        @php
          // Get previous reading value (last reading before billing period)
          $prevReading = \App\Models\MeterReading::where('meter_id', $invoice->meter_id)
            ->where('capture_time', '<', $invoice->billing_period_start)
            ->orderByDesc('capture_time')
            ->value('value') ?? 0;

          $currentReading = \App\Models\MeterReading::where('meter_id', $invoice->meter_id)
            ->where('capture_time', '<=', $invoice->billing_period_end)
            ->orderByDesc('capture_time')
            ->value('value') ?? 0;

          // Total usage in liters
          $totalLiters = (float)$invoice->total_usage * 1000;
        @endphp
        <tr>
          <td style="text-align:left;padding-left:8px;">Water Supplied / Vai na'e tufaki atu</td>
          <td>{{ number_format($prevReading, 0) }}</td>
          <td>{{ number_format($currentReading, 0) }}</td>
          <td>{{ number_format($totalLiters, 0) }}</td>
          <td>
            @if($invoice->items->count() > 0)
              ${{ number_format($invoice->items->first()->unit_rate / 1000, 5) }}
            @else
              —
            @endif
          </td>
          <td style="font-weight:700;">${{ number_format($invoice->subtotal, 2) }}</td>
        </tr>
        @if($invoice->items->count() > 1)
          @foreach($invoice->items as $item)
            <tr>
              <td style="text-align:left;padding-left:8px;font-size:8.5px;color:#444;">
                {{ $item->description }}
              </td>
              <td>—</td>
              <td>—</td>
              <td style="font-size:9px;">{{ number_format($item->quantity * 1000, 0) }} L</td>
              <td>${{ number_format($item->unit_rate / 1000, 5) }}</td>
              <td style="font-weight:600;">${{ number_format($item->line_total, 2) }}</td>
            </tr>
          @endforeach
        @endif
      </tbody>
    </table>
    <div class="ud-total">
      <span>Total for Water this month / Mo'ua 'Vai Ki he Mahina ni</span>
      <span>${{ number_format($invoice->subtotal, 2) }}</span>
    </div>
  </div>

  {{-- ══ TAX NOTE ════════════════════════════════════════════ --}}
  <div class="tax-note">
    <strong>Consumption Tax</strong><br>
    Domestic Consumers: &lt;20,001 litres - 15% Consumption Tax on Meter Charge only<br>
    Domestic Consumers: &gt;20,001 litres - 15% Consumption Tax<br>
    Commercial Consumers: 15% Consumption Tax applies to all charges
  </div>

  {{-- ══ FOOTER STAMP ════════════════════════════════════════ --}}
  <div class="footer-stamp">
    TWBYAdmin070415 &nbsp;|&nbsp; Generated: {{ now()->format('d M Y H:i') }} &nbsp;|&nbsp; TWB Billing System
  </div>

  </div>

</div>

</body>
</html>
