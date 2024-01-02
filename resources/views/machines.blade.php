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
        .plugin_not_open { background-color: yellow; }
        .pc_not_open { background-color: grey; }
        .failed { background-color: red; }

        /* 添加表格样式 */
        table {
            width: 50%;
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
<h3>Machines Status</h3>
<p>資料每10分鐘, 主機沒訊號監測30分鐘, 更新一次</p>
<p>綠燈 正常運作, 黃燈 大尾沒開, 紅燈 大尾沒回應, 灰色 主機沒訊號</p>
<p>私人 line token 請勿外流避免被不當使用</p>
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
