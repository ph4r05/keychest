<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'KeyChest') }}</title>
</head>
<body>
<div id="app" class="email-container">

    @yield('content')

</div>


</body>
</html>
