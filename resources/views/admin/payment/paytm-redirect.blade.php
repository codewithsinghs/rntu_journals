<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Redirecting to Paytm…</title>
</head>
<body onload="document.getElementById('paytmForm').submit();">
    <p style="font-family:sans-serif;text-align:center;margin-top:60px;">
        Redirecting to payment page. Please do not refresh or press back…
    </p>
    <form id="paytmForm" method="post" action="{{ $action }}">
        @foreach ($fields as $name => $value)
            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
        @endforeach
        <noscript><button type="submit">Continue to payment</button></noscript>
    </form>
</body>
</html>