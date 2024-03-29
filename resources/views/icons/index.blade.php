{{-- resources/views/icons/index.blade.php --}}

    <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Icon List</title>
    <style>
        .series {
            margin-bottom: 20px; /* 每个系列之间的间隔 */
        }
        .icons-container {
            display: flex;
            flex-wrap: wrap; /* 当一行满时，内容会移动到下一行 */
            gap: 10px; /* 图标之间的间隙 */
        }
        .icon {
            display: flex;
            flex-direction: column; /* 让图像和文字垂直排列 */
            align-items: center; /* 中心对齐图像和文字 */
            width: 100px; /* 根据需要调整图像宽度 */
        }
        .icon img {
            width: 100%; /* 使图像宽度填满容器 */
            height: auto; /* 保持图像的纵横比 */
        }
    </style>
</head>
<body>
<h1>Icon List</h1>
@foreach ($imageCounts as $series => $count)
    <div class="series">
        <h2>Series {{ $series + 1 }}</h2>
        <div class="icons-container">
            @for ($i = 1; $i <= $count; $i++)
                <div class="icon">
                    <img src="https://very6.tw/tools/{{ $series + 1 }}_{{ $i }}.png" alt="Series {{ $series + 1 }} Image {{ $i }}">
                    <p>{{ $series + 1 }}_{{ $i }}.png</p>
                </div>
            @endfor
        </div>
    </div>
@endforeach
</body>
</html>
