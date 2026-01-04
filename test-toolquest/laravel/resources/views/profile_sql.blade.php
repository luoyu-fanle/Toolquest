<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Profiel</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    {{-- <h1>Hallo, {{ $username }}</h1>\ --}}
    {{-- silent  --}}
    @if(isset($username))
        <h3>Welkom, {{ $username }}</h3>
        <p>Jouw rol is: {{ $role }} (ID: {{ $id }})</p>
    @endif

    @if(isset($result))
            <h4>Database Resultaten (Verbose Mode):</h4>
            <table border="1">
                
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($result as $user)
                        
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->username }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->role }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

    @endif


    {{-- @if(isset($quotes) && count($quotes) > 0)
        <h4>Jouw Quotes:</h4>
        <ul>
            @foreach($quotes as $quote)
                <li>{{ $quote->quote_text }}</li>
            @endforeach
        </ul>
     --}}


</body>
</html>