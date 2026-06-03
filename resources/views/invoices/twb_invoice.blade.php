<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
* { margin:0; padding:0; box-sizing:border-box; }

/* KEY FIX: Do NOT set height on body or html — DomPDF adds blank page
   when content exactly fills a fixed height. Let content flow naturally,
   and use page-break-after:avoid + overflow:hidden on wrapper.           */
@page {
  size: A4 portrait;
  margin: 5mm;
}

body {
  font-family: DejaVu Sans, Arial, sans-serif;
  font-size: 7.8pt;
  color: #111;
  background: #fff;
  /* NO height here — causes blank 2nd page in DomPDF */
}

.page {
  /*border: 0.7pt solid #444;*/
  padding: 3.5mm 5mm 3.5mm 5mm;
  /* No fixed height — let content determine it */
  page-break-after: avoid;
  page-break-inside: avoid;
}

/* ── HEADER ──────────────────────────────────────────── */
.hdr { width:100%; border-collapse:collapse; border-bottom:0.7pt solid #444; }
.hdr td { vertical-align:middle; padding-bottom:2mm; }
.logo-cell { text-align: right; width:46mm; }
.logo-cell img { width:24mm; height:auto; display:inline-block; }
.title-cell { text-align:center; }
.main-title { font-size:17pt; font-weight:bold; color:#0c3d7a; letter-spacing:0.5pt; display:block; }
.po-line    { font-size:7.5pt; color:#444; margin-top:1mm; display:block; }
.spacer-cell { width:26mm; }

/* ── INVOICE LABEL ───────────────────────────────────── */
.lbl-row { width:100%; border-collapse:collapse; margin-top:1mm; }
.lbl-row td { padding-bottom:0.6mm; }
.lbl-left  { font-style:italic; font-size:7pt; color:#333; width:40%; border-bottom:0.6pt solid #555; }
.lbl-right { width:60%; border-bottom:1pt solid #111; }

/* ── MAIN 2-COLUMN 35:65 ─────────────────────────────── */
.main-row { width:100%; border-collapse:collapse; margin-top:1.2mm; }
.main-row td { vertical-align:top; }
.col-left  { width:35%; padding-right:3mm; }
.col-right { width:65%; padding-left:1.5mm; }

/* ── LEFT: customer + chart ──────────────────────────── */
.cust-name { font-size:7.8pt; font-weight:bold; margin-bottom:0.8mm; }
.cust-addr { font-size:7pt; color:#333; line-height:1.6; margin-bottom:1.5mm; }
.chart-lbl { font-size:6pt; font-weight:bold; color:#111; line-height:1.4; margin-bottom:0.8mm; }
.chart-img { display:block; width:56mm; height:30mm; }
.chart-cap { font-size:5.5pt; color:#666; text-align:center; margin-top:0.6mm; }

/* Historical info directly below chart, no gap */
.hist-block { margin-top:1.5mm; font-size:5.8pt; color:#333; line-height:1.6; }
.hist-ttl   { font-size:6pt; font-weight:bold; color:#111; margin-bottom:0.5mm; line-height:1.3; }

/* ── RIGHT: account + financials ─────────────────────── */
.acct { width:100%; border-collapse:collapse; font-size:6.8pt; }
.acct td { padding:0.35mm 0; vertical-align:top; }
.acct .al { color:#444; width:56%; }
.acct .av { color:#111; font-weight:bold; text-align:right; }
.acct .an { font-size:8pt; }

.fin { font-size:6.8pt; margin-top:1.5mm; }
.fr  { width:100%; border-collapse:collapse; margin-bottom:0.5mm; }
.fr td { vertical-align:top; }
.fn  { width:7mm; font-weight:bold; }
.fl  { color:#333; }
.fv  { text-align:right; white-space:nowrap; min-width:14mm; }
.fvb { font-weight:bold; }
.f-hr  { border-top:0.3pt solid #aaa; margin:0.9mm 0; }
.f-thr { border-top:0.8pt solid #111; margin:0.9mm 0; }
.bal-tbl { width:100%; border-collapse:collapse; font-size:6.8pt; margin:0.6mm 0; }
.bal-tbl td { vertical-align:top; }
.bal-l { padding-left:7mm; font-weight:bold; line-height:1.4; }
.bal-v { text-align:right; font-weight:bold; white-space:nowrap; min-width:14mm; }
.tot-tbl { width:100%; border-collapse:collapse; font-size:7.8pt; font-weight:bold; }
.tot-tbl td { vertical-align:middle; padding:0.4mm 0; }
.tot-v { text-align:right; white-space:nowrap; }

/* Notes */
.notes-ttl { font-size:6.8pt; font-weight:bold; margin:1mm 0 0.6mm; }
.nt  { width:100%; border-collapse:collapse; font-size:5.8pt; margin-bottom:0.8mm; }
.nt td { vertical-align:top; }
.nn  { width:5.5mm; font-weight:bold; }
.ntx { color:#333; line-height:1.55; }

/* ── DIVIDER & USAGE TABLE ───────────────────────────── */
.div-line { border-top:0.7pt solid #333; margin:1.8mm 0 1.5mm; }
.u-ttl { font-size:6.5pt; font-weight:bold; margin-bottom:1.2mm; }
.u-tbl { width:100%; border-collapse:collapse; }
.u-tbl th {
  background:#c8d4e8; color:#1a1a2e; font-weight:bold; font-size:5.8pt;
  padding:1.3mm 1mm; border:0.5pt solid #666;
  text-align:center; vertical-align:middle;
}
.u-tbl th.tl { text-align:left; }
.u-tbl td {
  border:0.5pt solid #777; padding:1.3mm 1mm;
  text-align:center; vertical-align:middle; font-size:7pt;
}
.u-tbl td.tl { text-align:left; }
.u-tot { width:100%; border-collapse:collapse; font-size:7pt; font-weight:bold; margin-top:1.2mm; }
.u-tot td { vertical-align:middle; }
.u-tot .utr { text-align:right; border-bottom:0.4pt solid #444; }
.tax-fn { margin-top:1.5mm; font-size:5.8pt; color:#444; line-height:1.55; }
</style>
</head>
<body>
<div class="page">

{{-- ══ HEADER ══════════════════════════════════════════ --}}
<table class="hdr">
  <tr>
    <td class="logo-cell">&nbsp;</td>
    <td class="title-cell">
      <table>
        <tr>
          <td class="title-cell">
            <img src="data:image/jpeg;base64,{{ $logoBase64 }}" style="width:24mm;height:auto;display:block;">
          </td>
          <td class="title-cell">
            <span class="main-title">TONGA WATER BOARD</span>
            <span class="po-line">PO Box 92, Nuku'alofa, TONGA</span>
          </td>
        </tr>
      </table>
    </td>
    <td class="spacer-cell">&nbsp;</td>
  </tr>
</table>

{{-- ══ INVOICE LABEL ════════════════════════════════════ --}}
<table class="lbl-row">
  <tr>
    <td class="lbl-left">Consumption Tax Invoice / Statement</td>
    <td class="lbl-right">&nbsp;</td>
  </tr>
</table>

{{-- ══ MAIN 2-COL 35:65 ════════════════════════════════ --}}
<table class="main-row">
<tr>

{{-- ── LEFT 35% ──────────────────────────────────────── --}}
<td class="col-left">
  <div class="cust-name">{{ strtoupper($invoice->customer->name) }}</div>
  <div class="cust-addr">
    {{ strtoupper($invoice->customer->block_number ?? '') }}<br>
    {{ strtoupper($invoice->customer->address ?? '') }}
  </div>

  <div class="chart-lbl">Customer Water Supply<br>Monthly Usage (6 Months)</div>
  <img src="data:image/png;base64,{{ $chartBase64 }}" class="chart-img">
  <div class="chart-cap">Month / Mahina</div>

  {{-- Historical info — zero gap below chart --}}
  <div class="hist-block">
    <div class="hist-ttl">Historical Water Supply Information<br>(Hisitolia 'oe Vai na'e Tufaki atu)</div>
    Previous 6 Reading Dates Average<br>
    Water Supplied - {{ number_format($historyTotal) }} litres<br>
    'Avalisi 'oe Vai na'e Tufaki atu he fo'i<br>
    'aho lau 'e 6 ki mu'a - {{ number_format($historyTotal) }} lita<br>
    Current Water Supplied - {{ number_format($currentSupply) }} litres<br>
    Vai na'e Tufaki atu he mahina ni - {{ number_format($currentSupply) }} lita<br>
    Average Water Supplied - {{ number_format($historyAvg) }} litres<br>
    Faka'avalisi 'oe Vai na'e Tufaki atu 'ihe 'aho - {{ number_format($historyAvg) }} lita
  </div>
</td>

{{-- ── RIGHT 65% ──────────────────────────────────────── --}}
<td class="col-right">

  <table class="acct">
    <tr><td class="al">Customer Account:</td><td class="av an">{{ $invoice->customer->account_number }}</td></tr>
    <tr><td class="al">Invoice Date:</td><td class="av">{{ $invoice->issued_at?->format('d M y') ?? now()->format('d M y') }}</td></tr>
    <tr><td class="al">For Water Supplied From / Ki he vai na'e tufaki mei he</td><td class="av"></td></tr>
    <tr><td class="al"></td><td class="av">{{ $invoice->billing_period_start->format('d/m/y') }} to / ki he {{ $invoice->billing_period_end->format('d/m/y') }}</td></tr>
    <tr><td class="al">Account Enquiries / Faka'eke'eke mo'ua:</td><td class="av"></td></tr>
    <tr><td class="al">Fax:</td><td class="av">23299</td></tr>
    <tr><td class="al">Office:</td><td class="av">23518</td></tr>
    <tr><td class="al">Emergencies (After Hours):</td><td class="av">23095</td></tr>
    <tr><td class="al">TIN:</td><td class="av">24438 / 257118</td></tr>
  </table>

  <div class="fin">
    <table class="fr"><tr>
      <td class="fn">(1)</td>
      <td class="fl">Opening Balance</td>
      <td class="fv fvb">${{ number_format($openingBalance,2) }}Cr</td>
    </tr></table>
    <table class="fr"><tr>
      <td class="fn"></td>
      <td class="fl">Payment Received</td>
      <td class="fv">${{ number_format($paymentReceived,2) }}</td>
    </tr></table>

    <div class="f-hr"></div>
    <table class="bal-tbl"><tr>
      <td class="bal-l"><strong>Balance Before Current Charges / Palanisi</strong><br><strong>kimu'a he Mo'ua lolotonga</strong></td>
      <td class="bal-v">${{ number_format(abs($openingBalance-$paymentReceived),2) }}{{ ($openingBalance-$paymentReceived)>=0?'Cr':'Dr' }}</td>
    </tr></table>
    <div class="f-hr"></div>

    <table class="fr"><tr><td class="fn">(2)</td><td class="fl">Jobs / Ngaue na'e Fakahoko</td><td class="fv"></td></tr></table>
    <table class="fr"><tr><td class="fn"></td><td class="fl">Reconnection Fees / Totongi Hoko</td><td class="fv"></td></tr></table>
    <table class="fr"><tr>
      <td class="fn"></td>
      <td class="fl" style="font-size:6pt;">Current month Water Charges (please see (1) below for details)<br>/ Mo'ua vai ki he mahina ni (vakai ki hono fakaikiiki 'i he (1) 'i lalo)</td>
      <td class="fv">${{ number_format($invoice->subtotal,2) }}</td>
    </tr></table>
    <table class="fr"><tr><td class="fn"></td><td class="fl">Less {{ $discountPct }}% discount</td><td class="fv">${{ number_format($invoice->discount_amount,2) }}</td></tr></table>
    <table class="fr"><tr><td class="fn"></td><td class="fl">Consumption Tax {{ $taxPct }}%</td><td class="fv">${{ number_format($invoice->tax_amount,2) }}</td></tr></table>

    <div class="f-thr"></div>
    <table class="tot-tbl"><tr>
      <td>Total Amount Due / Fakakatoa Mo'ua ke totongi</td>
      <td class="tot-v">${{ number_format($invoice->balance_due,2) }}</td>
    </tr></table>
    <div class="f-thr" style="margin-top:0.8mm;"></div>

    {{-- Notes --}}
    <div class="notes-ttl">Notes:</div>
    <table class="nt"><tr><td class="nn">(1)</td><td class="ntx">If any part of the Past Due Amount remains unpaid your water supply will be disconnected without further notice.<br><em>Kapau 'oku 'i ai ha toenga mo'ua 'oku te'eki totongi mai 'e tu'usi atu leva ho'o mo'u'anga vai 'ikai toe fanogonongo atu.</em></td></tr></table>
    <table class="nt"><tr><td class="nn">(2)</td><td class="ntx">The Current Water Bill Amount is due for payment within 30 days of bill date.<br><em>Koe mo'ua lolotonga ke totongi 'i loto he 'aho 'e 30 mei he 'aho 'oe mo'ua ni.</em></td></tr></table>
    <table class="nt"><tr><td class="nn">(3)</td><td class="ntx">Please check your historical water usage and internal water supply for any unnecessary increase.<br><em>Kataki 'o fakapapau'i 'ae lahi ho'o ngaue'aki vai mei he hisitolia ho'o ngaue'aki vai pea mo vakai'i ho'o mo'uanga vai 'i loto 'api 'oku malu.</em></td></tr></table>
  </div>

</td>
</tr>
</table>{{-- /main-row --}}

{{-- ══ DIVIDER ════════════════════════════════════════ --}}
<div class="div-line"></div>

{{-- ══ USAGE TABLE ════════════════════════════════════ --}}
<div class="u-ttl">(1) Current Month Water Charges Usage Details / Ko e fakaikiiki 'eni 'o e mo'ua vai ki he mahina ni</div>
<table class="u-tbl">
  <thead>
    <tr>
      <th class="tl" style="width:18%">Item/Fakaikiiki</th>
      <th style="width:16%">Previous Reading<br>/ Lau Faka'osi</th>
      <th style="width:16%">Present Reading<br>/ Mo'ua ko eni</th>
      <th style="width:22%">Units Supplied /<br>'Iuniti na'e Tufaki atu</th>
      <th style="width:13%">Rate<br>/ Totongi</th>
      <th style="width:15%">Amount<br>/ Fakakatoa</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td class="tl">Water Supplied</td>
      <td>{{ number_format($previousReading) }}</td>
      <td>{{ number_format($presentReading) }}</td>
      <td>{{ number_format($unitsSupplied) }}</td>
      <td>${{ number_format($ratePerUnit,5) }}</td>
      <td>${{ number_format($invoice->subtotal,2) }}</td>
    </tr>
  </tbody>
</table>

<table class="u-tot">
  <tr>
    <td><strong>Total for Water this month / Mo'ua 'Vai Ki he Mahina ni</strong></td>
    <td class="utr"><strong>${{ number_format($invoice->subtotal,2) }}</strong></td>
  </tr>
</table>

<div class="tax-fn">
  <strong>Consumption Tax</strong>&nbsp;&nbsp;<br>
  Domestic Consumers: &lt;20,001 litres - 15% on Meter Charge only <br>
  Domestic Consumers: &gt;20,001 litres - 15% <br>
  Commercial Consumers: 15% on all charges
</div>

</div>{{-- /page --}}
</body>
</html>
