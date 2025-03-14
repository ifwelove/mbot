<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>YouTube 頻道列表</title>
    {{-- Tailwind CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
<h1 class="text-2xl font-bold mb-4">YouTube 頻道列表 (快取版)</h1>

@if(empty($channelsData))
    <p class="text-red-500">目前沒有快取資料，請先執行相關指令或產生快取檔。</p>
@else

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead>
            <tr class="bg-gray-200">
                <th class="py-2 px-4 border-b text-left">Channel ID</th>
                <th class="py-2 px-4 border-b text-left">頻道名稱</th>
                <th class="py-2 px-4 border-b text-right">訂閱數</th>
                <th class="py-2 px-4 border-b text-left">縮圖</th>
            </tr>
            </thead>
            <tbody>
            @foreach($channelsData as $channelId => $info)
                <tr class="hover:bg-gray-100">
                    <td class="py-2 px-4 border-b">
                        {{ $channelId }}
                    </td>
                    <td class="py-2 px-4 border-b">
                        {{ $info['title'] ?? '未知頻道' }}
                    </td>
                    <td class="py-2 px-4 border-b text-right">
                        {{-- 將 subs 數字格式化，或直接顯示 --}}
                        {{ number_format($info['subscriberCount'] ?? 0) }}
                    </td>
                    <td class="py-2 px-4 border-b">
                        {{-- 取高畫質縮圖 (high) 或 medium --}}
                        @php
                            $thumbs = $info['thumbnails'] ?? [];
                            $imgUrl = $thumbs['high']['url'] ?? ($thumbs['default']['url'] ?? null);
                        @endphp
                        @if($imgUrl)
                            <img src="{{ $imgUrl }}" alt="Thumb" class="w-16 h-16 object-cover rounded-full">
                        @else
                            <span class="text-gray-400">No Thumbnail</span>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

@endif
</body>
</html>
