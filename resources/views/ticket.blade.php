<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ticket</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/ticket.css') }}">
    @vite('resources/js/print-qz.js')
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
            <button id="btn-qz-print" class="btn btn-sm btn-success">Print (QZ)</button>
            <form id="form-escpos" action="{{ route('print.escpos') }}" method="POST" class="d-inline">
                @csrf
                <input type="hidden" name="number" value="{{ $ticket['number'] }}">
                <input type="hidden" name="transaction" value="{{ $ticket['transaction'] ?? ($ticket['category'] ?? '') }}">
                <input type="hidden" name="priority" value="{{ $ticket['priority'] ?? '' }}">
                <input type="hidden" name="generated_at" value="{{ $ticket['generated_at'] ?? '' }}">
                <button type="submit" class="btn btn-sm btn-outline-success">Print (Server)</button>
            </form>
        </div>
    </div>

    <script>
        // Try QZ Tray first for cross-platform client printing.
        const btn = document.getElementById('btn-qz-print');
        btn?.addEventListener('click', async () => {
            const ticket = {
                number: @json($ticket['number']),
                transaction: @json($ticket['transaction'] ?? ($ticket['category'] ?? '')),
                priority: @json($ticket['priority'] ?? ''),
                generated_at: @json($ticket['generated_at'] ?? ''),
            };
            try {
                await window.QZPrint.initQZ();
                // Use the system default printer; change if needed.
                const printers = await window.QZPrint.qz.printers.list();
                const defaultPrinter = printers?.[0] || null;
                if (!defaultPrinter) throw new Error('No printers found via QZ');
                await window.QZPrint.printTicketWithQZ(defaultPrinter, ticket);
                alert('Sent to printer via QZ Tray');
            } catch (e) {
                console.warn('QZ print failed, falling back', e);
                window.print();
            }
        });
    </script>
</body>
</html>
