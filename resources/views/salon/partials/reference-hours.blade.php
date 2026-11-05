@forelse($salon['profile']['hours'] ?? [] as $hour)
    <p>{{ $hour['days'] }}:<br>{{ $hour['time'] }}</p>
@empty
    <p>{{ $salon['profile']['hours_note'] ?? '' }}</p>
@endforelse
