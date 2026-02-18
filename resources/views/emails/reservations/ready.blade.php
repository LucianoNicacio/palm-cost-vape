<x-mail::message>
# Your Order is Ready for Pickup!

Hi {{ $customer->name }},

Great news! Your reservation **{{ $reservation->confirmation_number }}** is ready and waiting for you.

<x-mail::panel>
**IMPORTANT: Please pick up your order within 24 hours.**

Your pickup deadline is: **{{ $pickupDeadline->format('l, F j, Y \a\t g:i A') }}**

Orders not picked up within 24 hours will be automatically cancelled.
</x-mail::panel>

## Order Summary

<x-mail::table>
| Item | Qty | Price |
|:-----|:---:|------:|
@foreach($items as $item)
| {{ $item->product_name }} | {{ $item->quantity }} | ${{ number_format($item->total_price, 2) }} |
@endforeach
| | **Total** | **${{ number_format($reservation->total_price, 2) }}** |
</x-mail::table>

## Pickup Location

**{{ $storeName }}**
{{ $storeAddress }}, {{ $storeCity }}
{{ $storePhone }}

**Store Hours:** {{ $storeHours }}

Please bring a **valid photo ID** when picking up your order.

<x-mail::button :url="$viewUrl">
View Reservation
</x-mail::button>

Thank you for choosing {{ $storeName }}!
</x-mail::message>
