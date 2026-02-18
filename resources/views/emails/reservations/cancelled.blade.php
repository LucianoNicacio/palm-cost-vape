<x-mail::message>
# Reservation Cancelled

Hi {{ $customer->name }},

We're sorry to let you know that your reservation **{{ $reservation->confirmation_number }}** has been cancelled.

@if($isAutoExpired)
<x-mail::panel>
Your reservation was automatically cancelled because it was not picked up within 24 hours of being marked as ready.
</x-mail::panel>
@else
We apologize for any inconvenience this may cause.
@endif

## Order Details

<x-mail::table>
| Item | Qty | Price |
|:-----|:---:|------:|
@foreach($items as $item)
| {{ $item->product_name }} | {{ $item->quantity }} | ${{ number_format($item->total_price, 2) }} |
@endforeach
| | **Total** | **${{ number_format($reservation->total_price, 2) }}** |
</x-mail::table>

If you'd like to place a new order, you can visit our website or stop by the store.

Have questions? Please don't hesitate to contact us at **{{ $storePhone }}**.

<x-mail::button :url="$viewUrl">
View Reservation
</x-mail::button>

We hope to serve you again soon.
</x-mail::message>
