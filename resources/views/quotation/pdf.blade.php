<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: 'Noto Sans SC', sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        .header { text-align: center; }
        .logo { width: 300px; height: auto; }
        .notice { text-align: center; width: 500px; height: auto; }
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
        <td>編號</td>
        <td>搬運項目</td>
        <td>金額</td>
    </tr>
    </thead>
    <tbody>
    @foreach ($data['items'] as $index => $item)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $item['name'] }}</td>
            <td>${{ number_format($item['amount']) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<p>備註： {{ $data['note'] }}</p>
<img src="{{ public_path('images/notice.jpg') }}" class="notice" alt="樂遷搬家">
</body>
</html>
