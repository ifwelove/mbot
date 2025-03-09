<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        /*body { font-family: 'NotoSansTC', 'DejaVu Sans', sans-serif; }*/
        body {
            font-family: 'Noto Sans SC', sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        .header { text-align: center; }
        .logo { width: 150px; height: auto; }
        .table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .table th, .table td { border: 1px solid black; padding: 8px; text-align: left; }
    </style>

</head>
<body>
<div class="header">
    <img src="{{ public_path('images/movepro.png') }}" class="logo" alt="樂遷搬家">
    <h2>報價單</h2>
</div>

<p>客戶姓名： {{ $data['customer_name'] }}</p>
<p>電話： {{ $data['phone'] }}</p>
<p>訂單編號： {{ $data['order_no'] }}</p>
<p>搬出地址： {{ $data['from_address'] }}</p>
<p>搬運日期： {{ $data['move_date'] }}</p>
<p>搬入地址： {{ $data['to_address'] }}</p>
<p>接洽人員： {{ $data['contact_person'] }}</p>

<table class="table">
    <thead>
    <tr>
        <th>編號</th>
        <th>搬運項目</th>
        <th>金額</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td>1</td>
        <td>{{ $data['items'] }}</td>
        <td>{{ $data['amount'] }}</td>
    </tr>
    </tbody>
</table>

<p>備註： {{ $data['note'] }}</p>
<p><img src="{{ public_path('images/notice.png') }}" class="logo"></p>

</body>
</html>
