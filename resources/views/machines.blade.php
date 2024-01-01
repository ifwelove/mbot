<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Machines Status</title>
    <style>
        .status-icon {
            height: 20px;
            width: 20px;
            border-radius: 50%;
            display: inline-block;
        }
        .success { background-color: green; }
        .plugin-not-open { background-color: yellow; }
        .pc-not-open { background-color: grey; }
        .failed { background-color: red; }

        /* 添加表格样式 */
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px; /* 调整间隔大小 */
        }
        th, td {
            padding: 10px; /* 单元格内边距 */
            text-align: left;
        }
    </style>

</head>
<body>
<h1>Machines Status</h1>
<table>
    <tr>
        <th>電腦代號</th>
        <th>狀態</th>
        <th>最後更新時間</th>
    </tr>
    @foreach ($machines as $machine)
        <tr>
            <td>{{ $machine['mac'] }}</td>
            <td>
                <span class="status-icon {{ $machine['data']['status'] }}"></span>
                {{ $machine['data']['status'] }}
            </td>
            <td>{{ $machine['data']['last_updated'] }}</td>
        </tr>
    @endforeach
</table>
</body>
</html>
