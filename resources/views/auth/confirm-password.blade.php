<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Password</title>
</head>
<body>
    <main>
        <h1>{{ __('Confirm Password') }}</h1>
        <form method="POST" action="{{ url('/confirm-password') }}">
            @csrf
            <label for="password">{{ __('Password') }}</label>
            <input id="password" type="password" name="password" required autocomplete="current-password">
            <button type="submit">{{ __('Confirm') }}</button>
        </form>
    </main>
</body>
</html>
