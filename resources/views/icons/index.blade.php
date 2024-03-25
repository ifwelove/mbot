{{-- resources/views/icons/index.blade.php --}}

    <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Icon List</title>
    <style>
        .icons-container {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-start;
            gap: 16px; /* 调整图标之间的间隙 */
        }
        .icon {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100px; /* 调整图标宽度 */
        }
        .icon img {
            width: 100%; /* 图片宽度占满容器 */
            height: auto; /* 保持图片比例 */
        }
    </style>
</head>
<body>
<h1>Icon List</h1>
<div class="icons-container">
    @foreach ($files as $file)
        <div class="icon">
            <img src="{{ asset('storage/tools/' . basename($file)) }}" alt="{{ basename($file) }}">
            <p>{{ basename($file) }}</p>
        </div>
    @endforeach
</div>
</body>
</html>
