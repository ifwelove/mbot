{{-- resources/views/icons/index.blade.php --}}

    <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Icon List</title>
</head>
<body>
<h1>Icon List</h1>
@foreach ($imageCounts as $series => $count)
    <div class="series">
        <h2>Series {{ $series + 1 }}</h2>
        @for ($i = 1; $i <= $count; $i++)
            <img src="https://very6.tw/tools/{{ $series + 1 }}_{{ $i }}.png" alt="Series {{ $series + 1 }} Image {{ $i }}">
            <p>{{ $series + 1 }}_{{ $i }}.png</p>
        @endfor
    </div>
@endforeach
</body>
</html>
