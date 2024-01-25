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
<h3>Very6-大尾崩潰監視者</h3>
<a target="_blank" href="https://very6.tw/大尾崩潰檢測check_status_20240104_v0906.rar">一般版下載點</a>
<a target="_blank" href="https://drive.google.com/file/d/17OAEMUqbV8p5rdG-TsXxQRoTdWRwD19J/view?usp=sharing">鑽石版下載點</a>
<a target="_blank" href="https://docs.google.com/document/d/19y_lxsepZpKKQ8x-AjpNptyVy2-QzwYA35n8DoVtwfI/edit">教學文件</a>
<a target="_blank" href="https://line.me/ti/g2/5gBZGGhG_e3jylabmmkSQbpqW3PamjCxY490YQ">歡迎加入 Line 群討論</a>
<p>資料每10分鐘, 主機沒訊號監測30分鐘, 更新一次, 遊戲維修時間不推播, 私人 line token 請勿外流避免被不當使用</p>
<p>綠燈 正常運作, 黃燈 大尾沒開, 紅燈 大尾沒回應, 灰色 主機沒訊號</p>
<p>使用期限：{{ $user['date'] }}, 可使用台數：{{ $user['amount'] }}</p>
<p>共有礦場 {{ $machines_total }} 座, 有打幣機正在挖礦中 {{ $dnplayer_running_total }} / {{ $dnplayer_total }}</p>
<table>
    <tr>
        <th>電腦代號</th>
        <th>狀態</th>
        <th>模擬器數量</th>
        <th>MAC</th>
        <th>最後更新時間</th>
        <th></th>
    </tr>
    @foreach ($machines as $machine)
        <tr>
            <td>{{ $machine['pc_name'] }}</td>
            <td>
                <span class="status-icon {{ $machine['data']['status'] }}"></span>
                {{ $machine['data']['status'] }}
            </td>
            <td>{{ $machine['dnplayer_running'] }}/{{ $machine['dnplayer'] }}</td>
            <td>{{ $machine['mac'] }}</td>
            <td>{{ $machine['data']['last_updated'] }}</td>
            <td>
                <!-- 删除按钮 -->
                <button class="delete-btn" data-token="{{ $token }}" data-mac="{{ $machine['mac'] }}">重置數據-10分鐘後更新</button>
            </td>
        </tr>
    @endforeach
</table>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
    $(document).ready(function() {
        $('.delete-btn').click(function() {
            var token = $(this).data('token');
            var mac = $(this).data('mac');

            $.ajax({
                url: '/delete-machine', // 这是处理删除请求的路由
                method: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    token: token,
                    mac: mac
                },
                success: function(response) {
                    // 处理成功响应
                    alert(response.message);
                    location.reload(); // 重新加载页面
                },
                error: function(response) {
                    // 处理错误响应
                    alert("Error: " + response.responseText);
                }
            });
        });
    });
</script>
</body>
</html>
