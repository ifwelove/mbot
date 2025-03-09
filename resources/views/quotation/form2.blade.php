<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>樂遷報價單</title>
    <style>
        body {
            font-family: 'Noto Sans SC', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            background: #fff;
            padding: 20px;
            margin: 0 auto;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            color: #333;
        }
        label {
            display: block;
            font-weight: bold;
            margin-top: 10px;
        }
        input, textarea {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
        }
        .items-container {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .item-group {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .item-group input {
            flex: 2;
        }
        .item-group input[type="text"] {
            flex: 3;
        }
        button {
            display: block;
            width: 100%;
            padding: 10px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 18px;
            cursor: pointer;
            margin-top: 15px;
        }
        button:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>報價單填寫</h2>
    <form action="{{ url('/pdf') }}" method="POST">
        @csrf
        <label>客戶姓名: <input type="text" name="customer_name" value="" required></label>
        <label>電話: <input type="text" name="phone" value="" required></label>
        <label>訂單編號: <input type="text" name="order_no" value="" required></label>
        <label>搬出地址: <input type="text" name="from_address" value="" required></label>
        <label>搬運日期: <input type="text" name="move_date" value="" required></label>
        <label>搬入地址: <input type="text" name="to_address" value="" required></label>
        <label>接洽人員: <input type="text" name="contact_person" value="浩" required></label>

        <label>搬運項目：</label>
        <div class="items-container">
            @for ($i = 1; $i <= 10; $i++)
                <div class="item-group">
                    <input type="text" name="items[{{ $i }}][name]" placeholder="搬運項目名稱">
                    <input type="number" name="items[{{ $i }}][amount]" placeholder="金額" min="0">
                </div>
            @endfor
        </div>

        <label>備註: <textarea name="note" rows="3"></textarea></label>
        <button type="submit">產生報價單</button>
    </form>
</div>
</body>
</html>
