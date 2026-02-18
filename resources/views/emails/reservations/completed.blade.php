<x-mail::message>
# Thank You for Your Purchase!

Hi {{ $customer->name }},

Your reservation **{{ $reservation->confirmation_number }}** has been completed. We appreciate your business!

## Order Summary

<x-mail::table>
| Item | Qty | Price |
|:-----|:---:|------:|
@foreach($items as $item)
| {{ $item->product_name }} | {{ $item->quantity }} | ${{ number_format($item->total_price, 2) }} |
@endforeach
| | **Total** | **${{ number_format($reservation->total_price, 2) }}** |
</x-mail::table>

We hope you enjoy your purchase! We'd love to see you again at **{{ $storeName }}**.

Have questions about your order? Give us a call at {{ $storePhone }}.

<x-mail::button :url="$viewUrl">
View Reservation
</x-mail::button>

See you next time!
</x-mail::message>
