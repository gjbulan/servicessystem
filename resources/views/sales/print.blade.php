<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $sale->sale_number }}</title>
        <style>
            body { color: #111827; font-family: Arial, sans-serif; margin: 0; padding: 32px; }
            .actions { margin-bottom: 24px; }
            .button { background: #111827; border-radius: 6px; color: #fff; display: inline-block; font-size: 12px; font-weight: 700; letter-spacing: .06em; padding: 10px 14px; text-decoration: none; text-transform: uppercase; }
            .muted { color: #6b7280; }
            .header { display: flex; justify-content: space-between; gap: 24px; margin-bottom: 32px; }
            h1 { font-size: 28px; margin: 0 0 8px; }
            h2 { font-size: 16px; margin: 0 0 8px; }
            table { border-collapse: collapse; width: 100%; }
            th { background: #f9fafb; color: #4b5563; font-size: 12px; text-align: left; text-transform: uppercase; }
            th, td { border-bottom: 1px solid #e5e7eb; padding: 10px; }
            .right { text-align: right; }
            .totals { margin-left: auto; margin-top: 24px; width: 320px; }
            .totals div { display: flex; justify-content: space-between; padding: 8px 0; }
            .total { border-top: 1px solid #111827; font-weight: 700; }
            @media print {
                body { padding: 0; }
                .actions { display: none; }
            }
        </style>
    </head>
    <body>
        <div class="actions">
            <a href="#" onclick="window.print(); return false;" class="button">{{ __('Print') }}</a>
            <a href="{{ route('sales.show', $sale) }}" class="button">{{ __('Back') }}</a>
        </div>

        <div class="header">
            <div>
                <h1>{{ $sale->company?->name }}</h1>
                <p class="muted">{{ $sale->company?->email }}</p>
                <p class="muted">{{ $sale->company?->phone }}</p>
                <p class="muted">{{ $sale->company?->address }}</p>
            </div>
            <div class="right">
                <h2>{{ __('Receipt / Invoice') }}</h2>
                <p>{{ $sale->sale_number }}</p>
                <p class="muted">{{ $sale->sale_date?->format('M d, Y') }}</p>
                <p class="muted">{{ $statuses[$sale->status] ?? ucfirst($sale->status) }}</p>
            </div>
        </div>

        <div class="header">
            <div>
                <h2>{{ __('Bill To') }}</h2>
                <p>{{ $sale->customer?->name ?? __('Walk-in customer') }}</p>
                @if ($sale->customer)
                    <p class="muted">{{ $sale->customer->phone }}</p>
                    <p class="muted">{{ $sale->customer->email }}</p>
                    <p class="muted">{{ $sale->customer->address }}</p>
                @endif
            </div>
            <div class="right">
                <h2>{{ __('Branch') }}</h2>
                <p>{{ $sale->branch?->name }}</p>
                <p class="muted">{{ $sale->branch?->address }}</p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>{{ __('Item') }}</th>
                    <th>{{ __('SKU') }}</th>
                    <th class="right">{{ __('Qty') }}</th>
                    <th class="right">{{ __('Unit Price') }}</th>
                    <th class="right">{{ __('Total') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sale->items as $item)
                    <tr>
                        <td>
                            {{ $item->item_name_snapshot }}
                            @if ($item->variant_name_snapshot)
                                <br><span class="muted">{{ $item->variant_name_snapshot }}</span>
                            @endif
                        </td>
                        <td>{{ $item->sku_snapshot ?? '-' }}</td>
                        <td class="right">{{ number_format((float) $item->quantity, 2) }}</td>
                        <td class="right">{{ number_format((float) $item->unit_price, 2) }}</td>
                        <td class="right">{{ number_format((float) $item->line_total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <div><span>{{ __('Subtotal') }}</span><span>{{ number_format((float) $sale->subtotal, 2) }}</span></div>
            <div><span>{{ __('Discount') }}</span><span>{{ number_format((float) $sale->discount_amount, 2) }}</span></div>
            <div><span>{{ __('Tax') }}</span><span>{{ number_format((float) $sale->tax_amount, 2) }}</span></div>
            <div class="total"><span>{{ __('Total') }}</span><span>{{ number_format((float) $sale->total, 2) }}</span></div>
            <div><span>{{ __('Amount Paid') }}</span><span>{{ number_format((float) $sale->amount_paid, 2) }}</span></div>
            <div><span>{{ __('Balance Due') }}</span><span>{{ number_format((float) $sale->balance_due, 2) }}</span></div>
        </div>

        @if ($sale->notes)
            <h2>{{ __('Notes') }}</h2>
            <p class="muted">{{ $sale->notes }}</p>
        @endif
    </body>
</html>
