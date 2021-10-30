@component('mail::message')
# Reservation Success!
You made reservation for
</br>
{{ $info['fullName'] }}({{ $info['phone'] }})

## Reservation Details:
@component('mail::table')
|||
| ------------- |-------------|
| Date:         | {{ $info['date'] }}      |
| Duration:         | {{ $info['duration'] }} (minutes)      |
| Seat Number:        | {{ $info['seatNumber'] }}      |
| Number of seats:         | {{ $info['numberOfSeats'] }}      |
| Unique Reservation ID:  | #{{ $info['id'] }} |
|||
@endcomponent

@endcomponent
