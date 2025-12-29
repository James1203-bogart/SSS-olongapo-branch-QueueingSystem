
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="0; url={{ url('/queueing_info') }}" />
    <title>Redirecting...</title>
</head>
<body>
    <script>
        window.location.href = "{{ url('/queueing_info') }}";
    </script>
    <noscript>
        <meta http-equiv="refresh" content="0; url={{ url('/queueing_info') }}" />
        <p>If you are not redirected, <a href="{{ url('/queueing_info') }}">click here</a>.</p>
    </noscript>
</body>
</html>