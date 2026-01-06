<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ticket</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/ticket.css') }}">
</head>
<body class="p-4">
    <div class="ticket">
        <div class="h5">Queue Ticket</div>
        <div class="ticket-number mt-3">{{ $ticket['number'] }}</div>
        <div class="ticket-meta mt-2">{{ ucfirst($ticket['priority'] ?? '') }} &middot; {{ $ticket['transaction'] ?? $ticket['category'] ?? '' }}</div>
    <div class="ticket-meta">Generated: {{ $ticket['generated_at'] ?? (isset($ticket['timestamp']) ? \Carbon\Carbon::parse($ticket['timestamp'])->format('h:i:s A') : '') }}</div>
        <div class="mt-3">
            <a href="/" class="btn btn-sm btn-secondary">Home</a>
            <a href="{{ url()->previous() }}" class="btn btn-sm btn-outline-primary">Back</a>
        </div>
    </div>

    @if(($ticket['mode'] ?? '') === 'printer')
    <script>
        // Auto-print for thermal printer flows. In real deployment this will trigger the printer.
        window.onload = function(){ window.print(); }
    </script>
    @endif
</body>
</html>
