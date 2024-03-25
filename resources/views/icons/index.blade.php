{{-- resources/views/icons/index.blade.php --}}

    <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Icon List</title>
    <style>
        .icon-list {
            display: flex; /* 使用Flexbox */
            flex-wrap: wrap; /* 允許項目換行 */
            list-style: none; /* 移除列表標記 */
            padding: 0; /* 移除預設的padding */
        }
        .icon-list li {
            margin: 10px; /* 在圖標之間增加一些空間 */
            text-align: center; /* 讓文本在圖標下方居中 */
        }
        .icon-list img {
            max-width: 100px; /* 限制圖標大小，視你的需求而定 */
            height: auto; /* 保持圖標的比例 */
        }
    </style>
</head>
<body>
<h1>Icon List</h1>
<ul class="icon-list">
    @foreach ($files as $file)
        <li>
            <img src="{{ Storage::disk('local')->url($file) }}" alt="{{ basename($file) }}">
            <p>{{ basename($file) }}</p>
        </li>
    @endforeach
</ul>
</body>
</html>
