<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>YouTube 頻道資訊</title>
</head>
<body>
<h1>頻道資訊</h1>
<ul>
    <li><strong>頻道 ID：</strong> {{ $channel['channelId'] }}</li>
    <li><strong>標題：</strong> {{ $channel['title'] }}</li>
    <li><strong>訂閱數：</strong> {{ number_format($channel['subscriberCount']) }}</li>
    <li><strong>影片數：</strong> {{ number_format($channel['videoCount']) }}</li>
    <li><strong>總觀看數：</strong> {{ number_format($channel['viewCount']) }}</li>
    <li>
        <strong>描述：</strong>
        <pre style="font-family:inherit; white-space:pre-wrap;">{{ $channel['description'] }}</pre>
    </li>
</ul>
</body>
</html>
<script>
    setInterval(() => {
        fetch('/api/youtube/channel/UCjMBtSoVSmqE2jTqwKh3ttg') // 你自己的API
            .then(response => response.json())
            .then(data => {
                document.getElementById('subscriberCount').innerText = data.subscriberCount;
                // ... 其他欄位就根據你的資料動態更新
            });
    }, 60 * 1000); // 每60秒更新一次
</script>
