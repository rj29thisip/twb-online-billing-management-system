<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>TWB Executive Summary — {{ $periodLabel }} [{{ $districtLabel }}]</title>
<style>
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'DejaVu Sans',Arial,sans-serif;font-size:10px;color:#1e293b;background:#fff;line-height:1.5;}
.header{background:linear-gradient(135deg,#0f2d4a 0%,#1a5276 100%);padding:26px 32px 20px;color:#fff;}
.hdr-inner{display:table;width:100%;}
.hdr-logo{display:table-cell;vertical-align:middle;width:72px;}
.hdr-logo img{width:58px;height:58px;object-fit:contain;}
.hdr-logo-ph{width:58px;height:58px;background:rgba(255,255,255,.15);border-radius:8px;display:inline-block;text-align:center;line-height:58px;font-size:8px;color:rgba(255,255,255,.7);}
.hdr-text{display:table-cell;vertical-align:middle;padding-left:14px;}
.hdr-org{font-size:10px;font-weight:bold;color:rgba(255,255,255,.7);text-transform:uppercase;letter-spacing:1px;margin-bottom:3px;}
.hdr-title{font-size:19px;font-weight:bold;color:#fff;margin-bottom:3px;}
.hdr-sub{font-size:9px;color:rgba(255,255,255,.6);}
.hdr-meta{display:table-cell;vertical-align:middle;text-align:right;font-size:9px;color:rgba(255,255,255,.65);white-space:nowrap;}
.hdr-meta .mv{font-size:10px;font-weight:bold;color:rgba(255,255,255,.9);margin-bottom:4px;}
.accent{height:4px;background:linear-gradient(90deg,#3498db,#1abc9c,#f39c12);}
.content{padding:20px 32px;}
.sec{font-size:10px;font-weight:bold;text-transform:uppercase;letter-spacing:.7px;color:#0f2d4a;border-bottom:2px solid #3498db;padding-bottom:4px;margin:18px 0 12px;}
.sec:first-child{margin-top:0;}
.kgrid{display:table;width:100%;border-collapse:separate;border-spacing:5px;margin-bottom:4px;}
.kr{display:table-row;}
.kc{display:table-cell;width:25%;background:#f8fafc;border:1px solid #e2e8f0;border-radius:5px;padding:10px 12px;vertical-align:top;}
.kc.bl{border-top:3px solid #3498db;}.kc.gr{border-top:3px solid #1abc9c;}.kc.or{border-top:3px solid #f39c12;}.kc.rd{border-top:3px solid #e74c3c;}
.kl{font-size:8px;color:#64748b;text-transform:uppercase;letter-spacing:.4px;margin-bottom:5px;}
.kv{font-size:17px;font-weight:bold;color:#0f2d4a;line-height:1;}
.ks{font-size:8px;color:#94a3b8;margin-top:3px;}
.badge{display:inline-block;font-size:8px;padding:2px 6px;border-radius:10px;margin-top:3px;font-weight:bold;}
.bg{background:#dcfce7;color:#166534;}.br{background:#fee2e2;color:#991b1b;}.bo{background:#fef3c7;color:#92400e;}.bb{background:#dbeafe;color:#1e40af;}
.rbar-wrap{background:#e2e8f0;border-radius:4px;height:9px;width:100%;margin-top:6px;overflow:hidden;}
.rbar-fill{height:9px;border-radius:4px;background:linear-gradient(90deg,#1abc9c,#3498db);}
.two{display:table;width:100%;margin-top:16px;}
.cl{display:table-cell;width:50%;vertical-align:top;}
.cr{display:table-cell;width:50%;vertical-align:top;padding-left:10px;}
.stbl{width:100%;border-collapse:collapse;font-size:9px;}
.stbl th{background:#0f2d4a;color:#fff;padding:6px 8px;text-align:left;font-size:8px;text-transform:uppercase;letter-spacing:.4px;}
.stbl th.tr{text-align:right;}
.stbl td{padding:6px 8px;border-bottom:1px solid #f1f5f9;vertical-align:middle;}
.stbl tr:nth-child(even) td{background:#f8fafc;}
.stbl td.tr{text-align:right;font-family:monospace;}
.tn{display:inline-block;padding:2px 6px;border-radius:10px;font-size:8px;font-weight:bold;}
.to{background:#fee2e2;color:#991b1b;}.tg{background:#dcfce7;color:#166534;}
.nd{text-align:center;color:#94a3b8;font-style:italic;padding:14px;}
.infonote{background:#eff6ff;border-left:3px solid #3498db;padding:8px 12px;font-size:8px;color:#1e40af;border-radius:0 4px 4px 0;margin-top:14px;}
.foot{margin-top:24px;padding:12px 32px;border-top:1px solid #e2e8f0;display:table;width:100%;}
.fl{display:table-cell;font-size:8px;color:#94a3b8;vertical-align:middle;}
.fr{display:table-cell;text-align:right;font-size:8px;color:#94a3b8;vertical-align:middle;}
.bold{font-weight:bold;} .red{color:#991b1b;} .green{color:#166534;} .blue{color:#1e40af;}
</style>
</head>
<body>
<div class="header">
  <div class="hdr-inner">
    <div class="hdr-logo">
      @if($logoData)<img src="{{ $logoData }}" alt="TWB">
      @else<div class="hdr-logo-ph">TWB</div>@endif
    </div>
    <div class="hdr-text">
      <div class="hdr-org">Tonga Water Board</div>
      <div class="hdr-title">Executive Summary</div>
      <div class="hdr-sub">{{ $periodLabel }} &nbsp;·&nbsp; {{ $districtLabel }}</div>
      <div class="hdr-sub" style="font-size:9px;opacity:.7;">As per {{ $generatedAt }}</div>
    </div>
    <div class="hdr-meta">
      <div class="mv">Generated on</div>
      <div>{{ $generatedFull }}</div>
      <div style="margin-top:6px;" class="mv">Prepared by</div>
      <div>{{ auth()->user()->name ?? 'Administrator' }}</div>
    </div>
  </div>
</div>
<div class="accent"></div>

<div class="content">

  <div class="sec">Financial Overview — {{ $periodLabel }}</div>
  <div class="kgrid"><div class="kr">
    <div class="kc bl"><div class="kl">Total Invoiced</div><div class="kv">T${{ number_format($totalInvoiced,2) }}</div><div class="ks">{{ $invoiceCount }} invoice(s) issued</div></div>
    <div class="kc gr"><div class="kl">Total Collected</div><div class="kv">T${{ number_format($totalPaid,2) }}</div><div class="ks">{{ $paidCount }} payment(s)</div><div><span class="badge bg">{{ $collectionRate }}% collection rate</span></div></div>
    <div class="kc or"><div class="kl">Outstanding (Overdue)</div><div class="kv">T${{ number_format($totalOutstanding,2) }}</div><div class="ks">{{ $overdueCount }} overdue invoice(s)</div>@if($overdueCount>0)<div><span class="badge br">Requires attention</span></div>@endif</div>
    <div class="kc bl"><div class="kl">Total Customers</div><div class="kv">{{ number_format($totalCustomers) }}</div>@if($newCustomers>0)<div><span class="badge bg">+{{ $newCustomers }} new this month</span></div>@else<div class="ks">No new customers</div>@endif</div>
  </div></div>
  <div style="font-size:8.5px;color:#64748b;margin-top:8px;">Collection Rate — {{ $collectionRate }}%<div class="rbar-wrap"><div class="rbar-fill" style="width:{{ min($collectionRate,100) }}%;"></div></div></div>

  <div class="sec">Operational Overview</div>
  <div class="kgrid"><div class="kr">
    <div class="kc bl"><div class="kl">Meter Readings</div><div class="kv">{{ number_format($totalReadings) }}</div><div class="ks">Captured this period</div></div>
    <div class="kc gr"><div class="kl">Total Usage</div><div class="kv">{{ number_format($totalUsage) }}</div><div class="ks">Litres consumed</div></div>
    <div class="kc {{ $anomalyCount>0?'rd':'gr' }}"><div class="kl">Anomalies Detected</div><div class="kv">{{ $anomalyCount }}</div>@if($anomalyCount>0)<div><span class="badge br">Unusual usage events</span></div>@else<div><span class="badge bg">No anomalies</span></div>@endif</div>
    <div class="kc or"><div class="kl">Invoices Unpaid</div><div class="kv">{{ $unpaidCount }}</div><div class="ks">This period</div></div>
  </div></div>

  <div class="two">
    <div class="cl">
      <div class="sec" style="margin-top:0;">Overdue Invoices (Top 10)</div>
      @if($overdueInvoices->isEmpty())<div class="nd">No overdue invoices — all clear!</div>
      @else
      <table class="stbl">
        <thead><tr><th>Customer</th><th>Invoice #</th><th class="tr">Amount</th><th class="tr">Due Date</th></tr></thead>
        <tbody>
          @foreach($overdueInvoices as $inv)
          <tr>
            <td><div class="bold">{{ $inv->customer->name??'—' }}</div><div style="font-size:8px;color:#94a3b8;">{{ $inv->customer->account_number??'' }}</div></td>
            <td style="font-size:8px;color:#94a3b8;">{{ $inv->invoice_number }}</td>
            <td class="tr red bold">T${{ number_format($inv->total_amount,2) }}</td>
            <td class="tr"><span class="tn to">{{ \Carbon\Carbon::parse($inv->due_date)->format('d M Y') }}</span></td>
          </tr>
          @endforeach
        </tbody>
      </table>
      @endif
    </div>
    <div class="cr">
      <div class="sec" style="margin-top:0;">Top 5 by Usage</div>
      @if($topCustomersByUsage->isEmpty())<div class="nd">No usage data for this period.</div>
      @else
      <table class="stbl">
        <thead><tr><th>Customer</th><th class="tr">Usage (L)</th></tr></thead>
        <tbody>
          @foreach($topCustomersByUsage as $i=>$row)
          <tr>
            <td>
              <div class="bold">#{{ $i+1 }} {{ $row->meter->customer->name??'Unknown' }}</div>
              <div style="font-size:8px;color:#94a3b8;">{{ $row->meter->customer->account_number??'' }}</div>
            </td>
            <td class="tr blue bold">{{ number_format($row->total_usage) }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
      @endif
    </div>
  </div>

  <div class="infonote">
    <strong>Note:</strong> This report covers <strong>{{ $periodStart->format('d M Y') }}</strong> to <strong>{{ $periodEnd->format('d M Y') }}</strong>.
    Financial figures are in Tongan Pa'anga (T$). Data is accurate as of {{ $generatedFull }}.
  </div>
</div>

<div class="foot">
  <div class="fl">Tonga Water Board &nbsp;·&nbsp; OWBMS — Online Water Billing Management System<br>System-generated document. No signature required.</div>
  <div class="fr">CONFIDENTIAL &nbsp;·&nbsp; Internal use only<br>{{ $generatedFull }}</div>
</div>
</body>
</html>
