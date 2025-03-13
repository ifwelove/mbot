<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-S77TGXYZGF"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-S77TGXYZGF');
    </script>

    <meta charset="UTF-8">
    <title>Machines Status</title>

    <!-- Tailwind CSS CDN -->
{{--    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.2.7/dist/tailwind.min.css" rel="stylesheet">--}}
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
    <style>
        /* 狀態小圓點 */
        .status-icon {
            @apply h-5 w-5 rounded-full inline-block;
        }
        .success {
            background-color: green;
        }
        .plugin_not_open {
            background-color: yellow;
        }
        .pc_not_open {
            background-color: grey;
        }
        .failed {
            background-color: red;
        }
        /* 隱藏下拉內容的基礎 class */
        .hidden-content {
            display: none;
        }
    </style>
</head>
<body class="bg-gray-100">
<div class="container mx-auto py-4 px-2">
    <h3 class="text-xl font-bold mb-4">Very6-大尾崩潰監視者</h3>
    <!-- 頂部按鈕列（保留） -->
    <div class="flex flex-wrap items-center space-x-2 mb-4">
        <a href="javascript:void(0)" class="bg-white text-gray-700 py-2 px-4 rounded shadow">
            重新整理於: <span id="countdown" class="font-bold">120</span> 秒
        </a>
        <a href="javascript:void(0)" id="pauseButton" class="bg-red-500 text-white py-2 px-4 rounded shadow">
            暫停倒數
        </a>
        <a target="_blank" href="https://docs.google.com/document/d/19y_lxsepZpKKQ8x-AjpNptyVy2-QzwYA35n8DoVtwfI/edit"
           class="bg-blue-500 text-white py-2 px-4 rounded shadow">
            教學文件
        </a>
        <a target="_blank" href="https://line.me/ti/g2/5gBZGGhG_e3jylabmmkSQbpqW3PamjCxY490YQ"
           class="bg-green-500 text-white py-2 px-4 rounded shadow">
            歡迎加入 Line 群討論
        </a>
    </div>

    <!-- 其他說明文字區域 -->
    <p class="mb-2">大尾監控小程式 購買每台電腦每月50元...（此處省略原文案）。</p>
    <p class="mb-2">資料每10分鐘, 主機沒訊號監測30分鐘, 更新一次...（此處省略原文案）。</p>
    <p class="mb-2">綠燈 正常運作, 黃燈 大尾沒開, 紅燈 大尾沒回應, 灰色 主機沒訊號</p>
    <p class="mb-2">
        使用期限：{{ $user['date'] }} ，可使用台數：{{ $user['amount'] }}
    </p>
    <p class="mb-4">
        共有礦場 {{ $machines_total }} 座, 有打幣機正在挖礦中 {{ $dnplayer_running_total }} / {{ $dnplayer_total }}<br>
        全伺服器統計：{{ $money_total }}
        @if ($money_total!=0 && $dnplayer_total!=0)
            ，平均帳號打鑽數：{{ round($money_total / $dnplayer_total, 0) }}
        @endif
        ，各伺服器鑽石統計：
        <!-- 顯示資料按鈕（開啟 Modal） -->
        <button type="button"
                class="bg-blue-500 text-white py-2 px-4 rounded shadow"
                data-toggle="modal" data-target="#dataModal">
            顯示資料
        </button>
    </p>

    <!-- 收斂的「批次指令」按鈕（下拉式 or 顯示 / 隱藏） -->
    <div class="mb-6">
        <button id="toggleBatchCommands"
                class="bg-red-500 text-white py-2 px-4 rounded shadow">
            批次指令 ▼
        </button>
        <!-- 下拉內容容器（預設隱藏） -->
        <div id="batchCommands" class="hidden-content mt-2 space-x-2">
            <button class="command-btn-all-mac close_64_apk-btn bg-red-400 text-white py-2 px-2 rounded shadow"
                    data-token="{{ $token }}" data-command="close_64_apk">一鍵關閉自動檢查64apk</button>
            <button class="command-btn-all-mac open_64_apk-btn bg-red-400 text-white py-2 px-2 rounded shadow"
                    data-token="{{ $token }}" data-command="open_64_apk">一鍵開啟自動檢查64apk</button>
            <button class="command-btn-all-mac close_mpro-btn bg-red-400 text-white py-2 px-2 rounded shadow"
                    data-token="{{ $token }}" data-command="close_mpro">一鍵關閉大尾</button>
            <button class="command-btn-all-mac open_mpro-btn bg-red-400 text-white py-2 px-2 rounded shadow"
                    data-token="{{ $token }}" data-command="open_mpro">一鍵開啟大尾</button>
            <button class="command-btn-all-mac reopen_mpro-btn bg-red-400 text-white py-2 px-2 rounded shadow"
                    data-token="{{ $token }}" data-command="reopen_mpro">一鍵重開大尾</button>
            <button class="command-btn-all-mac sort_player-btn bg-red-400 text-white py-2 px-2 rounded shadow"
                    data-token="{{ $token }}" data-command="sort_player">一鍵排列模擬器</button>
            <button class="command-btn-all-mac reboot_pc-btn bg-red-400 text-white py-2 px-2 rounded shadow"
                    data-token="{{ $token }}" data-command="reboot_pc">一鍵重新開機</button>
            <button class="command-btn-all-mac copy_to_local-btn bg-red-400 text-white py-2 px-2 rounded shadow"
                    data-token="{{ $token }}" data-command="copy_to_local">一鍵雲端複製到本地</button>
            <button class="command-btn-all-mac open_update_mpro-btn bg-red-400 text-white py-2 px-2 rounded shadow"
                    data-token="{{ $token }}" data-command="open_update_mpro">一鍵開啟自動更新</button>
            <button class="command-btn-all-mac close_update_mpro-btn bg-red-400 text-white py-2 px-2 rounded shadow"
                    data-token="{{ $token }}" data-command="close_update_mpro">一鍵關閉自動更新</button>
            <button class="command-btn-all-mac reopen_monitor-btn bg-red-400 text-white py-2 px-2 rounded shadow"
                    data-token="{{ $token }}" data-command="reopen_monitor">一鍵重開監視器程式</button>
            <button class="command-btn-all-mac apk_install-btn bg-red-400 text-white py-2 px-2 rounded shadow"
                    data-token="{{ $token }}" data-command="apk_install">一鍵安裝apk</button>
            <button class="command-btn-all-mac open_exception_auto_reboot-btn bg-red-400 text-white py-2 px-2 rounded shadow"
                    data-token="{{ $token }}" data-command="open_exception_auto_reboot">一鍵開啟模擬器畫面異常自動重啟</button>
            <button class="command-btn-all-mac close_exception_auto_reboot-btn bg-red-400 text-white py-2 px-2 rounded shadow"
                    data-token="{{ $token }}" data-command="close_exception_auto_reboot">一鍵關閉模擬器畫面異常自動重啟</button>
            <button class="command-btn-all-mac close_all_player-btn bg-red-400 text-white py-2 px-2 rounded shadow"
                    data-token="{{ $token }}" data-command="close_all_player">一鍵關閉所有模擬器</button>
            <button class="command-btn-all-mac open_all_player-btn bg-red-400 text-white py-2 px-2 rounded shadow"
                    data-token="{{ $token }}" data-command="open_all_player">一鍵開啟所有模擬器</button>
        </div>
    </div>

    <!-- server 下拉 -->
    <div class="mb-4">
        <select name="server" class="border border-gray-300 rounded p-2">
            @foreach ($merges as $server => $total)
                @php
                    // 隨機顏色只是示範
                    $color = sprintf('#%06X', mt_rand(0, 0xFFFFFF));
                @endphp
                <optgroup label="{{ $server }}">
                    <option value="{{ $server }}" style="color: {{ $color }}">
                        {{ $server }}: {{ $total }}
                    </option>
                </optgroup>
            @endforeach
        </select>
    </div>

    <!-- 狀態下拉 -->
    <div class="mb-6">
        <select name="pc_status" class="border border-gray-300 rounded p-2">
            @foreach ($machines as $index => $machine)
                @if ($machine['data']['status'] !== 'success')
                    @php
                        $color = sprintf('#%06X', mt_rand(0, 0xFFFFFF));
                    @endphp
                    <optgroup label="{{ $machine['pc_name'] }}">
                        <option value="{{ $machine['pc_name'] }}" style="color: {{ $color }}">
                            {{ $machine['pc_name'] }}-{{ $machine['data']['status'] }}
                        </option>
                    </optgroup>
                @endif
            @endforeach
        </select>
    </div>

    <!-- 資料列表 Modal (可用 Alpine.js 或者簡單的 JS 控制) -->
    <div class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 items-center justify-center p-4 z-50"
         id="dataModal">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md mx-auto">
            <div class="p-4 border-b flex justify-between items-center">
                <h5 class="text-lg font-bold">資料列表</h5>
                <button class="text-gray-500" onclick="toggleModal('dataModal')">&times;</button>
            </div>
            <div class="p-4">
                <ul class="list-disc ml-4">
                    @foreach ($merges as $key => $value)
                        <li>{{ $key }}: {{ $value }}</li>
                    @endforeach
                </ul>
            </div>
            <div class="p-4 border-t text-right">
                <button class="bg-gray-300 text-gray-700 py-1 px-3 rounded"
                        onclick="toggleModal('dataModal')">關閉</button>
            </div>
        </div>
    </div>

    <!-- 每台機器的詳細資訊 Modal -->
    @foreach ($machines as $index => $machine)
        <div id="detailModal{{ $index }}"
             class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 items-center justify-center p-4 z-50">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-3xl mx-auto">
                <div class="p-4 border-b flex justify-between items-center">
                    <h5 class="text-lg font-bold">詳細資訊 - {{ $machine['pc_name'] }}</h5>
                    <button class="text-gray-500" onclick="toggleModal('detailModal{{ $index }}')">&times;</button>
                </div>
                <div class="p-4 overflow-auto">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead>
                        <tr>
                            <th class="px-2 py-2 text-left">#</th>
                            <th class="px-2 py-2 text-left">伺服器</th>
                            <th class="px-2 py-2 text-left">狀態</th>
                            <th class="px-2 py-2 text-left">鑽石數</th>
                            <th class="px-2 py-2 text-left">格子數量</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                        @foreach ($machine['role_list'] as $detailIndex => $detail)
                            <tr>
                                <td class="px-2 py-1">{{ $detailIndex + 1 }}</td>
                                <td class="px-2 py-1">{{ $detail[2] }}</td>
                                <td class="px-2 py-1">{{ $detail[4] }}</td>
                                <td class="px-2 py-1">{{ $detail[3] }}</td>
                                <td class="px-2 py-1">{{ $detail[5] }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t text-right">
                    <button class="bg-gray-300 text-gray-700 py-1 px-3 rounded"
                            onclick="toggleModal('detailModal{{ $index }}')">關閉</button>
                </div>
            </div>
        </div>
@endforeach

<!-- 主表格 -->
    <div class="overflow-x-auto bg-white rounded shadow">
        <table class="min-w-full divide-y divide-gray-300">
            <thead class="bg-gray-100">
            <tr>
                <th class="px-4 py-2 text-left">主機&狀態</th>
                <th class="px-4 py-2 text-left">帳號狀態</th>
                <th class="px-4 py-2 text-left">模擬器數量</th>
                <th class="px-4 py-2 text-left">鑽石(點選可複製)</th>
                <th class="px-4 py-2 text-left">卡號到期</th>
                <th class="px-4 py-2 text-left">最後更新時間</th>
                <th class="px-4 py-2 text-left">遠端控制</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
            @foreach ($machines as $index => $machine)
                <tr>
                    <td class="px-4 py-3 align-top">
                        <button type="button"
                                class="bg-blue-500 text-white py-1 px-2 rounded shadow mb-1"
                                onclick="toggleModal('detailModal{{ $index }}')">
                            {{ $machine['pc_name'] }}
                        </button>
                        <div class="text-sm text-gray-700">
                            <p>{{ $machine['data']['pro_version'] }}</p>
                            @if(isset($machine['data']['version']))
                                <p>{{ $machine['data']['version'] }}</p>
                            @endif
                        </div>
                        <div class="flex items-center space-x-2 mt-1">
                            <span class="status-icon {{ $machine['data']['status'] }}"></span>
                            <span class="text-sm">{{ $machine['data']['status'] }}</span>
                        </div>
                    </td>
                    <td class="px-4 py-3 align-top text-sm">
                        @foreach ($machine['rows'] as $status => $total)
                            {{ $status }}:{{ $total }}<br>
                        @endforeach
                        @if (isset($machine['m_pro_gg_count']) && $machine['m_pro_gg_count'] > 6)
                            <span class="text-red-500 font-bold">模擬器黑屏異常</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 align-top text-sm">
                        {{ $machine['dnplayer_running'] }}/{{ $machine['dnplayer'] }}
                    </td>
                    <td class="px-4 py-3 align-top text-sm">
                        @foreach ($machine['money_rows'] as $server => $items)
                            <button class="bg-yellow-400 text-black py-1 px-2 rounded shadow mb-1"
                                    onclick="copyToClipboard('#server-data-{{ $machine['pc_name'] }}-{{ $server }}')">
                                {{ $server }}:{{ $items['total'] }}
                            </button>
                            <div id="server-data-{{ $machine['pc_name'] }}-{{ $server }}"
                                 class="hidden">
                                {!! $items['rows'] !!}
                            </div>
                            <br>
                        @endforeach
                    </td>
                    <td class="px-4 py-3 align-top text-sm">
                        {{ $machine['card'] }}
                    </td>
                    <td class="px-4 py-3 align-top text-sm">
                        {{ $machine['data']['last_updated'] }}
                    </td>
                    <td class="px-4 py-3 align-top">
                        <!-- 遠端控制按鈕收斂在下拉內容 -->
                        <div class="relative inline-block text-left">
                            <button onclick="toggleCommandMenu('menu-{{ $index }}')"
                                    class="bg-red-500 text-white py-1 px-2 rounded shadow">
                                更多指令 ▼
                            </button>
                            <div id="menu-{{ $index }}"
                                 class="hidden-content absolute z-10 mt-2 w-48 bg-white border border-gray-200 rounded shadow">
                                <ul class="py-1">
                                    <li>
                                        <button class="command-btn block w-full text-left px-2 py-1 text-sm text-gray-700 hover:bg-gray-100"
                                                data-token="{{ $token }}" data-mac="{{ $machine['mac'] }}"
                                                data-command="close_all_player">關閉所有模擬器
                                        </button>
                                    </li>
                                    <li>
                                        <button class="command-btn block w-full text-left px-2 py-1 text-sm text-gray-700 hover:bg-gray-100"
                                                data-token="{{ $token }}" data-mac="{{ $machine['mac'] }}"
                                                data-command="close_64_apk">關閉自動檢查64apk
                                        </button>
                                    </li>
                                    <li>
                                        <button class="command-btn block w-full text-left px-2 py-1 text-sm text-gray-700 hover:bg-gray-100"
                                                data-token="{{ $token }}" data-mac="{{ $machine['mac'] }}"
                                                data-command="open_all_player">開啟所有模擬器
                                        </button>
                                    </li>
                                    <li>
                                        <button class="command-btn block w-full text-left px-2 py-1 text-sm text-gray-700 hover:bg-gray-100"
                                                data-token="{{ $token }}" data-mac="{{ $machine['mac'] }}"
                                                data-command="open_64_apk">開啟自動檢查64apk
                                        </button>
                                    </li>
                                    <li>
                                        <button class="command-btn block w-full text-left px-2 py-1 text-sm text-gray-700 hover:bg-gray-100"
                                                data-token="{{ $token }}" data-mac="{{ $machine['mac'] }}"
                                                data-command="close_mpro">關閉大尾
                                        </button>
                                    </li>
                                    <li>
                                        <button class="command-btn block w-full text-left px-2 py-1 text-sm text-gray-700 hover:bg-gray-100"
                                                data-token="{{ $token }}" data-mac="{{ $machine['mac'] }}"
                                                data-command="open_mpro">開啟大尾
                                        </button>
                                    </li>
                                    <li>
                                        <button class="command-btn block w-full text-left px-2 py-1 text-sm text-gray-700 hover:bg-gray-100"
                                                data-token="{{ $token }}" data-mac="{{ $machine['mac'] }}"
                                                data-command="reopen_mpro">重開大尾
                                        </button>
                                    </li>
                                    <li>
                                        <button class="command-btn block w-full text-left px-2 py-1 text-sm text-gray-700 hover:bg-gray-100"
                                                data-token="{{ $token }}" data-mac="{{ $machine['mac'] }}"
                                                data-command="reboot_pc">重新開機
                                        </button>
                                    </li>
                                    <li>
                                        <button class="command-btn block w-full text-left px-2 py-1 text-sm text-gray-700 hover:bg-gray-100"
                                                data-token="{{ $token }}" data-mac="{{ $machine['mac'] }}"
                                                data-command="sort_player">排列模擬器
                                        </button>
                                    </li>
                                    <li>
                                        <button class="command-btn block w-full text-left px-2 py-1 text-sm text-gray-700 hover:bg-gray-100"
                                                data-token="{{ $token }}" data-mac="{{ $machine['mac'] }}"
                                                data-command="copy_to_local">雲端複製到本地
                                        </button>
                                    </li>
                                    <li>
                                        <button class="command-btn block w-full text-left px-2 py-1 text-sm text-gray-700 hover:bg-gray-100"
                                                data-token="{{ $token }}" data-mac="{{ $machine['mac'] }}"
                                                data-command="open_update_mpro">開啟自動更新
                                        </button>
                                    </li>
                                    <li>
                                        <button class="command-btn block w-full text-left px-2 py-1 text-sm text-gray-700 hover:bg-gray-100"
                                                data-token="{{ $token }}" data-mac="{{ $machine['mac'] }}"
                                                data-command="close_update_mpro">關閉自動更新
                                        </button>
                                    </li>
                                    <li>
                                        <button class="command-btn block w-full text-left px-2 py-1 text-sm text-gray-700 hover:bg-gray-100"
                                                data-token="{{ $token }}" data-mac="{{ $machine['mac'] }}"
                                                data-command="reopen_monitor">重開監視器程式
                                        </button>
                                    </li>
                                    <li>
                                        <button class="command-btn block w-full text-left px-2 py-1 text-sm text-gray-700 hover:bg-gray-100"
                                                data-token="{{ $token }}" data-mac="{{ $machine['mac'] }}"
                                                data-command="apk_install">安裝apk
                                        </button>
                                    </li>
                                    <li>
                                        <button class="command-btn block w-full text-left px-2 py-1 text-sm text-gray-700 hover:bg-gray-100"
                                                data-token="{{ $token }}" data-mac="{{ $machine['mac'] }}"
                                                data-command="open_exception_auto_reboot">開啟畫面異常自動重啟
                                        </button>
                                    </li>
                                    <li>
                                        <button class="command-btn block w-full text-left px-2 py-1 text-sm text-gray-700 hover:bg-gray-100"
                                                data-token="{{ $token }}" data-mac="{{ $machine['mac'] }}"
                                                data-command="close_exception_auto_reboot">關閉畫面異常自動重啟
                                        </button>
                                    </li>
                                    <hr class="my-1">
                                    <li>
                                        <button class="delete-btn block w-full text-left px-2 py-1 text-sm text-red-600 hover:bg-gray-100"
                                                data-token="{{ $token }}" data-mac="{{ $machine['mac'] }}">
                                            重置網頁資料
                                        </button>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- jQuery (若不想依賴可以改寫純原生 JS) -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<!-- 用來取代原本 Bootstrap Modal 功能的簡易顯示/隱藏函式 -->
<script>
    function toggleModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal.classList.contains('hidden')) {
            // 顯示 Modal
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        } else {
            // 隱藏 Modal
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }

    // 批次指令區塊顯示/隱藏
    const toggleBatchCommandsBtn = document.getElementById('toggleBatchCommands');
    const batchCommandsDiv = document.getElementById('batchCommands');
    toggleBatchCommandsBtn.addEventListener('click', () => {
        if (batchCommandsDiv.classList.contains('hidden-content')) {
            batchCommandsDiv.classList.remove('hidden-content');
        } else {
            batchCommandsDiv.classList.add('hidden-content');
        }
    });

    // 顯示 / 隱藏機器各自的「更多指令」Menu
    function toggleCommandMenu(id) {
        const menu = document.getElementById(id);
        if (menu.classList.contains('hidden-content')) {
            menu.classList.remove('hidden-content');
        } else {
            menu.classList.add('hidden-content');
        }
    }

    // 複製到剪貼簿
    function copyToClipboard(element) {
        var text = $(element).html().replace(/<br\s*[\/]?>/gi, '\n');
        var $temp = $("<textarea>");
        $("body").append($temp);
        $temp.val(text).select();
        document.execCommand("copy");
        $temp.remove();
        alert("已複製");
    }

    // 倒數計時
    var seconds = 120;
    var isPaused = false;
    function updateTimer() {
        if (!isPaused) {
            seconds--;
            $('#countdown').text(seconds);
            if (seconds <= 0) {
                window.location.reload();
            }
        }
    }
    setInterval(updateTimer, 1000);
    $('#pauseButton').click(function() {
        isPaused = !isPaused;
        $(this).text(isPaused ? '恢復倒數' : '暫停倒數');
    });

    // AJAX 發送指令 (單台)
    $('.command-btn').click(function() {
        var token = $(this).data('token');
        var mac = $(this).data('mac');
        var command = $(this).data('command');

        $.ajax({
            url: '/store-command',
            method: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                token: token,
                mac: mac,
                command: command,
            },
            success: function(response) {
                alert(response.message);
                location.reload();
            },
            error: function(response) {
                alert("Error: " + response.responseText);
            }
        });
    });

    // AJAX 發送指令 (所有機器)
    $('.command-btn-all-mac').click(function() {
        var token = $(this).data('token');
        var command = $(this).data('command');

        $.ajax({
            url: '/store-all-mac-command',
            method: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                token: token,
                command: command,
            },
            success: function(response) {
                alert(response.message);
                location.reload();
            },
            error: function(response) {
                alert("Error: " + response.responseText);
            }
        });
    });

    // 重置網頁資料
    $('.delete-btn').click(function() {
        var token = $(this).data('token');
        var mac = $(this).data('mac');

        $.ajax({
            url: '/delete-machine',
            method: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                token: token,
                mac: mac
            },
            success: function(response) {
                alert(response.message);
                location.reload();
            },
            error: function(response) {
                alert("Error: " + response.responseText);
            }
        });
    });
</script>
</body>
</html>
