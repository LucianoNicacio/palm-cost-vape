<x-mail::message>
# Reservation Received!

Hi {{ $customer->name }},

We've received your reservation and it's being reviewed by our team. We'll notify you as soon as your order is ready for pickup.

**Confirmation Number:** {{ $reservation->confirmation_number }}

## Order Summary

<x-mail::table>
| Item | Qty | Price |
|:-----|:---:|------:|
@foreach($items as $item)
| {{ $item->product_name }} | {{ $item->quantity }} | ${{ number_format($item->total_price, 2) }} |
@endforeach
| | **Subtotal** | ${{ number_format($reservation->subtotal, 2) }} |
| | **Tax** | ${{ number_format($reservation->tax_amount, 2) }} |
| | **Total Due** | **${{ number_format($reservation->total_price, 2) }}** |
</x-mail::table>

## What Happens Next?

1. Our team will review and prepare your order
2. You'll receive an email when it's ready for pickup
3. Bring a **valid photo ID** to pick up your order

## Pickup Location

**{{ $storeName }}**
{{ $storeAddress }}, {{ $storeCity }}
{{ $storePhone }}

**Store Hours:** {{ $storeHours }}

<x-mail::button :url="$viewUrl">
View Reservation
</x-mail::button>

Thank you for choosing {{ $storeName }}!
</x-mail::message>
