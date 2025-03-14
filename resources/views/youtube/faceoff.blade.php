<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>頻道訂閱數</title>
    {{-- Tailwind CSS via CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800 flex flex-col items-center justify-center p-6">

<h1 class="text-2xl font-bold mb-6">YouTube 頻道訂閱數</h1>

<div class="flex flex-col md:flex-row items-center gap-8">

    {{-- ========== CHANNEL A ========== --}}
    <div class="bg-white rounded shadow p-6 w-72 flex flex-col items-center">
        {{-- 頻道圖 --}}
        <img
            class="w-24 h-24 rounded-full mb-4 border border-gray-300 object-cover"
            src="{{ $channelA['thumbnailUrl'] }}"
            alt="Channel A Picture"
        />

        <h2 class="text-xl font-semibold mb-4">
            {{ $channelA['title'] }}
        </h2>
        <p class="text-xl font-bold mb-2">
            訂閱數: {{ number_format($channelA['subscriberCount']) }}
        </p>
        <p class="text-gray-600 mb-1">影片數: {{ number_format($channelA['videoCount']) }}</p>
        <p class="text-gray-600">總觀看: {{ number_format($channelA['viewCount']) }}</p>
    </div>

    {{-- VS 標誌 --}}
    <div class="text-2xl font-bold text-gray-500">VS</div>

    {{-- ========== CHANNEL B ========== --}}
    <div class="bg-white rounded shadow p-6 w-72 flex flex-col items-center">
        {{-- 頻道圖 --}}
        <img
            class="w-24 h-24 rounded-full mb-4 border border-gray-300 object-cover"
            src="{{ $channelB['thumbnailUrl'] }}"
            alt="Channel B Picture"
        />

        <h2 class="text-xl font-semibold mb-4">
            {{ $channelB['title'] }}
        </h2>
        <p class="text-xl font-bold mb-2">
            訂閱數: {{ number_format($channelB['subscriberCount']) }}
        </p>
        <p class="text-gray-600 mb-1">影片數: {{ number_format($channelB['videoCount']) }}</p>
        <p class="text-gray-600">總觀看: {{ number_format($channelB['viewCount']) }}</p>
    </div>

</div>

{{-- 分出勝負區域 --}}
@php
    $subA = $channelA['subscriberCount'];
    $subB = $channelB['subscriberCount'];

    if ($subA > $subB) {
        $winner = $channelA['title'];
        $difference = $subA - $subB;
    } elseif ($subB > $subA) {
        $winner = $channelB['title'];
        $difference = $subB - $subA;
    } else {
        $winner = '平手';
        $difference = 0;
    }
@endphp

<div class="mt-8 text-center">
    @if($winner === '平手')
        {{-- 如果平手就不顯示差距 --}}
    @else
        <p class="text-xl font-bold text-red-600">
            差距 {{ number_format($difference) }} 訂閱
        </p>
    @endif
</div>

{{-- 倒數計時 + 按鈕區域 --}}
<div class="mt-6">
    <button
        id="countdown-button"
        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded font-semibold"
    >
        即將於 60 秒後重新整理
    </button>
</div>

<script>
    let countdown = 60;
    const button = document.getElementById("countdown-button");

    // 每秒更新倒數，並在倒數歸零時重新整理頁面
    const interval = setInterval(() => {
        countdown--;
        if (countdown <= 0) {
            clearInterval(interval);
            window.location.reload(); // 自動重新整理
        } else {
            button.innerText = `即將於 ${countdown} 秒後更新資訊`;
        }
    }, 1000);

    // 如果使用者想要提早重新整理，可以按按鈕
    button.addEventListener('click', () => {
        clearInterval(interval);
        window.location.reload();
    });
</script>

</body>
</html>
