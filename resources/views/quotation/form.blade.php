<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>報價單</title>
</head>
<body>
<h2>報價單填寫</h2>
<form action="{{ url('/quotation') }}" method="POST">
    @csrf
    <label>客戶姓名: <input type="text" name="customer_name" value="宋雯婷"></label><br>
    <label>電話: <input type="text" name="phone" value="0988178992"></label><br>
    <label>訂單編號: <input type="text" name="order_no" value="20250307001"></label><br>
    <label>搬出地址: <input type="text" name="from_address" value="平鎮區環南路二段192巷1號"></label><br>
    <label>搬運日期: <input type="text" name="move_date" value="2025/03/21(五)13:00"></label><br>
    <label>搬入地址: <input type="text" name="to_address" value="桃園市大溪區大漢溪多功能草皮公園"></label><br>
    <label>接洽人員: <input type="text" name="contact_person" value="浩"></label><br>
    <label>搬運項目: <textarea name="items">健走道具跟物資</textarea></label><br>
    <label>金額: <input type="text" name="amount" value="3500"></label><br>
    <label>備註: <textarea name="note">財團法人心路社會福利基金會 00968326</textarea></label><br>
    <button type="submit">產生 PDF</button>
</form>
</body>
</html>
