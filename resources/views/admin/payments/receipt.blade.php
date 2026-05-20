<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Receipt {{ $payment->receipt_number }}</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: Arial, sans-serif; font-size: 13px; color: #1a1a2e; padding: 30px; max-width: 400px; margin: 0 auto; }
    .header { text-align: center; border-bottom: 2px solid #1a73e8; padding-bottom: 16px; margin-bottom: 20px; }
    .header h1 { font-size: 18px; font-weight: 700; color: #1a73e8; }
    .header p  { font-size: 12px; color: #666; margin-top: 4px; }
    .receipt-num { text-align: center; font-size: 16px; font-weight: 700; margin-bottom: 20px; }
    .row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee; font-size: 13px; }
    .row .label { color: #666; }
    .row .value { font-weight: 500; color: #1a1a2e; }
    .amount-row { background: #e8f5e9; border-radius: 8px; padding: 14px; text-align: center; margin: 20px 0; }
    .amount-row .amt { font-size: 28px; font-weight: 700; color: #2e7d32; }
    .amount-row .lbl { font-size: 12px; color: #555; margin-top: 4px; }
    .footer { margin-top: 24px; text-align: center; font-size: 11px; color: #999; border-top: 1px dashed #ddd; padding-top: 16px; }
    .no-print { margin-bottom: 20px; }
    @media print { .no-print { display: none; } }
  </style>
</head>
<body>

  <div class="no-print">
    <button onclick="window.print()" style="padding:8px 20px;background:#1a73e8;color:#fff;border:none;border-radius:6px;cursor:pointer;">
      Print Receipt
    </button>
    <a href="{{ route('admin.payments.show', $payment) }}" style="margin-left:12px;color:#1a73e8;font-size:13px;">← Back</a>
  </div>

  <div class="header">
    <h1>TWB Water Billing</h1>
    <p>Tonga Water Board — Payment Receipt</p>
  </div>

  <div class="receipt-num">Receipt No: {{ $payment->receipt_number }}</div>

  <div class="amount-row">
    <div class="amt">T$ {{ number_format($payment->amount, 2) }}</div>
    <div class="lbl">Amount Received</div>
  </div>

  <div class="row"><span class="label">Date</span><span class="value">{{ $payment->payment_date->format('d M Y') }}</span></div>
  <div class="row"><span class="label">Customer</span><span class="value">{{ $payment->invoice->customer->name }}</span></div>
  <div class="row"><span class="label">Account #</span><span class="value">{{ $payment->invoice->customer->account_number }}</span></div>
  <div class="row"><span class="label">Invoice #</span><span class="value">{{ $payment->invoice->invoice_number }}</span></div>
  <div class="row"><span class="label">Billing Period</span><span class="value">{{ $payment->invoice->billing_period_start->format('M Y') }}</span></div>
  <div class="row"><span class="label">Payment Method</span><span class="value">{{ ucfirst(str_replace('_',' ',$payment->payment_method)) }}</span></div>
  @if($payment->reference_code)
    <div class="row"><span class="label">Reference</span><span class="value">{{ $payment->reference_code }}</span></div>
  @endif
  <div class="row"><span class="label">Remaining Balance</span><span class="value" style="{{ $payment->invoice->balance_due > 0 ? 'color:#e65100' : 'color:#2e7d32' }}">T$ {{ number_format($payment->invoice->balance_due, 2) }}</span></div>

  <div class="footer">
    <p>Thank you for your payment!</p>
    <p style="margin-top:4px;">Generated: {{ now()->format('d M Y H:i') }} | TWB Billing System</p>
  </div>

</body>
</html>
