{{-- resources/views/icons/index.blade.php --}}

    <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Icon List</title>
</head>
<body>
<h1>Icon List</h1>
<ul>
    @foreach ($files as $file)
        <li>
            <img src="{{ Storage::disk('local')->url($file) }}" alt="{{ basename($file) }}">
            <p>{{ basename($file) }}</p>
        </li>
    @endforeach
</ul>
</body>
</html>
