<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sales Report - {{ $periodLabel }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #1a1a1a; margin: 0; padding: 20px; }
        h1 { font-size: 20px; margin: 0 0 4px 0; }
        h2 { font-size: 14px; margin: 24px 0 8px 0; padding-bottom: 4px; border-bottom: 2px solid #16a34a; }
        .subtitle { color: #666; font-size: 11px; margin-bottom: 20px; }
        .stats-grid { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .stats-grid td { padding: 8px 12px; border: 1px solid #e5e7eb; width: 33.33%; }
        .stats-grid .label { color: #666; font-size: 10px; text-transform: uppercase; }
        .stats-grid .value { font-size: 16px; font-weight: bold; }
        .stats-grid .value.green { color: #16a34a; }
        table.data { width: 100%; border-collapse: collapse; font-size: 11px; }
        table.data th { background: #f3f4f6; text-align: left; padding: 6px 8px; border: 1px solid #e5e7eb; font-size: 10px; text-transform: uppercase; color: #666; }
        table.data td { padding: 6px 8px; border: 1px solid #e5e7eb; }
        table.data tr:nth-child(even) { background: #f9fafb; }
        .totals-row td { font-weight: bold; background: #f3f4f6 !important; }
        .text-right { text-align: right; }
        .generated { font-size: 9px; color: #999; margin-top: 20px; text-align: center; }
    </style>
</head>
<body>
    <h1>Palm Coast Vape — Sales Report</h1>
    <p class="subtitle">Period: {{ $periodLabel }} &middot; Generated {{ now()->format('M d, Y g:ia') }}</p>

    {{-- Summary Stats --}}
    <table class="stats-grid">
        <tr>
            <td>
                <div class="label">Total Revenue</div>
                <div class="value green">${{ number_format($stats['total_revenue'], 2) }}</div>
            </td>
            <td>
                <div class="label">Reservation Revenue</div>
                <div class="value">${{ number_format($stats['reservation_revenue'], 2) }}</div>
            </td>
            <td>
                <div class="label">In-Store Revenue</div>
                <div class="value">${{ number_format($stats['in_store_revenue'], 2) }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="label">Total Orders</div>
                <div class="value">{{ $stats['total_orders'] }}</div>
            </td>
            <td>
                <div class="label">Items Sold</div>
                <div class="value">{{ $stats['total_items_sold'] }}</div>
            </td>
            <td>
                <div class="label">Avg. Order Value</div>
                <div class="value">${{ number_format($stats['average_order_value'], 2) }}</div>
            </td>
        </tr>
    </table>

    {{-- Reservations --}}
    @if(count($reservations) > 0)
    <h2>Reservations ({{ count($reservations) }})</h2>
    <table class="data">
        <thead>
            <tr>
                <th>Date</th>
                <th>Confirmation #</th>
                <th>Customer</th>
                <th class="text-right">Items</th>
                <th class="text-right">Subtotal</th>
                <th class="text-right">Tax</th>
                <th class="text-right">Discount</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reservations as $r)
            <tr>
                <td>{{ $r->created_at->format('M d, Y') }}</td>
                <td>{{ $r->confirmation_number }}</td>
                <td>{{ $r->customer->name ?? 'Unknown' }}</td>
                <td class="text-right">{{ $r->item_count }}</td>
                <td class="text-right">${{ number_format($r->subtotal, 2) }}</td>
                <td class="text-right">${{ number_format($r->tax_amount, 2) }}</td>
                <td class="text-right">${{ number_format($r->reward_discount, 2) }}</td>
                <td class="text-right">${{ number_format($r->total_price, 2) }}</td>
            </tr>
            @endforeach
            <tr class="totals-row">
                <td colspan="4">Total</td>
                <td class="text-right">${{ number_format($reservations->sum('subtotal'), 2) }}</td>
                <td class="text-right">${{ number_format($reservations->sum('tax_amount'), 2) }}</td>
                <td class="text-right">${{ number_format($reservations->sum('reward_discount'), 2) }}</td>
                <td class="text-right">${{ number_format($reservations->sum('total_price'), 2) }}</td>
            </tr>
        </tbody>
    </table>
    @endif

    {{-- In-Store Sales --}}
    @if(count($inStoreSales) > 0)
    <h2>In-Store Sales ({{ count($inStoreSales) }})</h2>
    <table class="data">
        <thead>
            <tr>
                <th>Date</th>
                <th>Sale #</th>
                <th>Recorded By</th>
                <th class="text-right">Items</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($inStoreSales as $sale)
            <tr>
                <td>{{ $sale->created_at->format('M d, Y') }}</td>
                <td>#{{ $sale->id }}</td>
                <td>{{ $sale->recorder->name ?? 'Unknown' }}</td>
                <td class="text-right">{{ $sale->total_items }}</td>
                <td class="text-right">${{ number_format($sale->total_amount, 2) }}</td>
            </tr>
            @endforeach
            <tr class="totals-row">
                <td colspan="3">Total</td>
                <td class="text-right">{{ $inStoreSales->sum('total_items') }}</td>
                <td class="text-right">${{ number_format($inStoreSales->sum('total_amount'), 2) }}</td>
            </tr>
        </tbody>
    </table>
    @endif

    {{-- Top Products --}}
    @if(count($topProducts) > 0)
    <h2>Top Products</h2>
    <table class="data">
        <thead>
            <tr>
                <th>#</th>
                <th>Product</th>
                <th class="text-right">Qty Sold</th>
                <th class="text-right">Revenue</th>
            </tr>
        </thead>
        <tbody>
            @foreach($topProducts as $i => $product)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $product->name }}</td>
                <td class="text-right">{{ $product->total_quantity }}</td>
                <td class="text-right">${{ number_format($product->total_revenue, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <p class="generated">Palm Coast Vape and Glassware &middot; Generated by PCV Admin</p>
</body>
</html>
