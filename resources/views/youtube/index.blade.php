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

    {{-- ===== 快速導覽區 (錨點連結) ===== --}}
    <div class="mb-4 p-4 bg-white rounded shadow">
        <h2 class="text-lg font-bold mb-2">快速導覽</h2>
        <div class="flex flex-wrap gap-2">
            @foreach($channelsData as $channelId => $info)
                <a href="#channel-{{ $channelId }}"
                   class="px-3 py-1 bg-blue-100 text-blue-700 rounded hover:bg-blue-200"
                >
                    {{ $info['title'] }}
                </a>
            @endforeach
        </div>
    </div>

    {{-- ===== 資料表格 ===== --}}
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead>
            <tr class="bg-gray-200">
                <th class="py-2 px-4 border-b text-center"></th>
                <th class="py-2 px-4 border-b text-left">縮圖</th>
                <th class="py-2 px-4 border-b text-left">頻道名稱</th>
{{--                <th class="py-2 px-4 border-b text-left">訂閱數</th>--}}
            </tr>
            </thead>
            <tbody>
            @foreach($channelsData as $channelId => $info)
                <tr class="hover:bg-gray-100" id="channel-{{ $channelId }}">
                    <td class="py-2 px-4 border-b text-center font-semibold">
{{--                        {{ $loop->iteration }}--}}
                        {{ number_format($info['subscriberCount'] ?? 0) }}
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
{{--                    <td class="py-2 px-4 border-b text-left">--}}
{{--                        {{ number_format($info['subscriberCount'] ?? 0) }}--}}
{{--                    </td>--}}
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endif
</body>
</html>
