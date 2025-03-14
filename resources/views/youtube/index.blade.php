<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>YouTube 頻道列表</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
<h1 class="text-2xl font-bold mb-4">YouTube 頻道列表</h1>

@if(empty($channelsData))
    <p class="text-red-500">目前沒有資料</p>
@else
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead>
            <tr class="bg-gray-200">
                {{-- 新增：排名 --}}
                <th class="py-2 px-4 border-b text-center">排名</th>

                <th class="py-2 px-4 border-b text-left">縮圖</th>
                <th class="py-2 px-4 border-b text-left">頻道名稱</th>
                <th class="py-2 px-4 border-b text-right">訂閱數</th>
            </tr>
            </thead>
            <tbody>
            @foreach($channelsData as $channelId => $info)
                <tr class="hover:bg-gray-100">
                    {{-- 使用 $loop->iteration 顯示從 1 開始的編號 --}}
                    <td class="py-2 px-4 border-b text-center font-semibold">
                        {{ $loop->iteration }}
                    </td>

                    <td class="py-2 px-4 border-b">
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

                    <td class="py-2 px-4 border-b">
                        {{ $info['title'] }}
                    </td>

                    <td class="py-2 px-4 border-b text-right">
                        {{ number_format($info['subscriberCount'] ?? 0) }}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endif
</body>
</html>
