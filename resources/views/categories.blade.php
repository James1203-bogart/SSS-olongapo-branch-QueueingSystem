<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Choose Transaction</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/categories.css') }}">
</head>
<body class="p-4">
    <div class="container">
        <h3>{{ ucfirst($priority) }} - Select Transaction</h3>
        <p>Mode: {{ ucfirst($mode) }}</p>

        <div class="row">
            @foreach($transactions as $tx)
            <div class="col-md-4 mb-3">
                <form method="post" action="{{ route('ticket.generate') }}">
                    @csrf
                    <input type="hidden" name="mode" value="{{ $mode }}">
                    <input type="hidden" name="priority" value="{{ $priority }}">
                    <input type="hidden" name="transaction" value="{{ $tx['id'] }}">
                    <button class="btn btn-outline-primary w-100">{{ $tx['name'] }}</button>
                </form>
            </div>
            @endforeach
        </div>

        <div class="mt-3">
            <a href="/" class="btn btn-link">Back to Home</a>
        </div>
    </div>
</body>
</html>
