<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'NotoSansTC', 'DejaVu Sans', sans-serif; }
        .header { text-align: center; }
        .logo { width: 150px; height: auto; }
        .table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .table th, .table td { border: 1px solid black; padding: 8px; text-align: left; }
    </style>

</head>
<body>
<div class="header">
    <img src="{{ public_path('images/logo.png') }}" class="logo" alt="公司 Logo">
    <h2>報價單</h2>
</div>

<p><strong>客戶姓名：</strong> {{ $data['customer_name'] }}</p>
<p><strong>電話：</strong> {{ $data['phone'] }}</p>
<p><strong>訂單編號：</strong> {{ $data['order_no'] }}</p>
<p><strong>搬出地址：</strong> {{ $data['from_address'] }}</p>
<p><strong>搬運日期：</strong> {{ $data['move_date'] }}</p>
<p><strong>搬入地址：</strong> {{ $data['to_address'] }}</p>
<p><strong>接洽人員：</strong> {{ $data['contact_person'] }}</p>

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

<p><strong>備註：</strong> {{ $data['note'] }}</p>
</body>
</html>
