<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Toolquest-jwt</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            {{ $errors->first() }}
        </div>
    @endif

    <h1> THIS IS AN HOME PAGE </h1> 
    <a href="{{ route('login') }}">
    <button type="button">LOGIN</button>
    </a>
    <a href="{{ route('signup') }}">
    <button type="button">SIGNUP</button>
    </a>
    <a href="{{ route('profile.jwt') }}">
    <button type="button">PROFILE</button>
    </a>
</body>
</html>