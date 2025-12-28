<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    @if($errors->any())
        <div class="alert alert-danger">
            {{ $errors->first() }}
        </div>
    @endif
    
    <form method="POST" action="/api/signup">
         @csrf
        <label>Email</label>
        <input type="text" id="email" name="email">
        <br>
        <label>Username</label>
        <input type="text" id="username" name="username">
        <br>
        <label>Password</label>
        <input type="password" id="password" name="password">
        <br>
        <input type="submit" value="Sign Up">
    </form>
</body>