<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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

    <!-- Bootstrap CSS (此處以 v4.3.1 為例；若要用 v5 請自行替換CDN) -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <!--
    若要使用 Bootstrap 5：
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    -->

    <style>
        /* 狀態小圓點 */
        .status-icon {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: inline-block;
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

        /* 「更多指令」下拉選單最小寬度 */
        .command-dropdown-menu {
            min-width: 200px;
        }
        /* 一鍵指令按鈕容器 */
        .batch-btn-group > button {
            margin: 4px 4px 4px 0;
        }
        /* 強制表格在小螢幕可捲動 */
        .table-responsive {
            overflow-x: auto;
        }
    </style>
</head>
<body class="bg-light">

<div class="container py-4">
    <h3 class="mb-3">Very6-大尾崩潰監視者</h3>

    <!-- 頂部按鈕列 -->
    <div class="mb-3">
        <a href="javascript:void(0)" class="btn btn-secondary">
            重新整理於: <span id="countdown">120</span> 秒
        </a>
        <a href="javascript:void(0)" id="pauseButton" class="btn btn-danger">
            暫停倒數
        </a>
        <a target="_blank" href="https://docs.google.com/document/d/19y_lxsepZpKKQ8x-AjpNptyVy2-QzwYA35n8DoVtwfI/edit" class="btn btn-info">
            教學文件
        </a>
        <a target="_blank" href="https://line.me/ti/g2/5gBZGGhG_e3jylabmmkSQbpqW3PamjCxY490YQ" class="btn btn-success">
            歡迎加入 Line 群討論
        </a>
    </div>

    <!-- 簡介與說明 -->
    <p>大尾監控小程式 購買每台電腦每月50元...（此處省略原文案）。</p>
    <p>資料每10分鐘, 主機沒訊號監測30分鐘...（此處省略原文案）。</p>
    <p>綠燈 正常運作, 黃燈 大尾沒開, 紅燈 大尾沒回應, 灰色 主機沒訊號</p>
    <p>使用期限：{{ $user['date'] }} ，可使用台數：{{ $user['amount'] }}</p>
    <p>
        共有礦場 {{ $machines_total }} 座, 有打幣機正在挖礦中 {{ $dnplayer_running_total }} / {{ $dnplayer_total }} <br>
        全伺服器統計：{{ $money_total }}
        @if ($money_total!=0 && $dnplayer_total!=0)
            ，平均帳號打鑽數：{{ round($money_total / $dnplayer_total, 0) }}
        @endif
        ，各伺服器鑽石統計：
        <!-- 「顯示資料」按鈕，點擊開啟 dataModal -->
        <button class="btn btn-primary" data-toggle="modal" data-target="#dataModal">
            顯示資料
        </button>
    </p>

    <!-- 「批次指令」折疊按鈕 -->
    <div class="mb-4">
        <button class="btn btn-warning" data-toggle="collapse" data-target="#batchCommands" aria-expanded="false" aria-controls="batchCommands">
            批次指令 ▼
        </button>
        <!-- 折疊內容 -->
        <div id="batchCommands" class="collapse mt-2">
            <div class="batch-btn-group d-flex flex-wrap">
                <button class="command-btn-all-mac btn btn-danger" data-token="{{ $token }}" data-command="close_64_apk">一鍵關閉自動檢查64apk</button>
                <button class="command-btn-all-mac btn btn-danger" data-token="{{ $token }}" data-command="open_64_apk">一鍵開啟自動檢查64apk</button>
                <button class="command-btn-all-mac btn btn-danger" data-token="{{ $token }}" data-command="close_mpro">一鍵關閉大尾</button>
                <button class="command-btn-all-mac btn btn-danger" data-token="{{ $token }}" data-command="open_mpro">一鍵開啟大尾</button>
                <button class="command-btn-all-mac btn btn-danger" data-token="{{ $token }}" data-command="reopen_mpro">一鍵重開大尾</button>
                <button class="command-btn-all-mac btn btn-danger" data-token="{{ $token }}" data-command="sort_player">一鍵排列模擬器</button>
                <button class="command-btn-all-mac btn btn-danger" data-token="{{ $token }}" data-command="reboot_pc">一鍵重新開機</button>
                <button class="command-btn-all-mac btn btn-danger" data-token="{{ $token }}" data-command="copy_to_local">一鍵雲端複製到本地</button>
                <button class="command-btn-all-mac btn btn-danger" data-token="{{ $token }}" data-command="open_update_mpro">一鍵開啟自動更新</button>
                <button class="command-btn-all-mac btn btn-danger" data-token="{{ $token }}" data-command="close_update_mpro">一鍵關閉自動更新</button>
                <button class="command-btn-all-mac btn btn-danger" data-token="{{ $token }}" data-command="reopen_monitor">一鍵重開監視器程式</button>
                <button class="command-btn-all-mac btn btn-danger" data-token="{{ $token }}" data-command="apk_install">一鍵安裝apk</button>
                <button class="command-btn-all-mac btn btn-danger" data-token="{{ $token }}" data-command="open_exception_auto_reboot">一鍵開啟模擬器畫面異常自動重啟</button>
                <button class="command-btn-all-mac btn btn-danger" data-token="{{ $token }}" data-command="close_exception_auto_reboot">一鍵關閉模擬器畫面異常自動重啟</button>
                <button class="command-btn-all-mac btn btn-danger" data-token="{{ $token }}" data-command="close_all_player">一鍵關閉所有模擬器</button>
                <button class="command-btn-all-mac btn btn-danger" data-token="{{ $token }}" data-command="open_all_player">一鍵開啟所有模擬器</button>
            </div>
        </div>
    </div>

    <!-- server 下拉 -->
    <div class="form-group">
        <label>選擇伺服器：</label>
        <select name="server" class="form-control w-auto d-inline-block">
            @foreach ($merges as $server => $total)
                @php
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
    <div class="form-group">
        <label>顯示狀態：</label>
        <select name="pc_status" class="form-control w-auto d-inline-block">
            @foreach ($machines as $index => $machine)
                @if ($machine['data']['status'] !== 'success')
                    @php
                        $color = sprintf('#%06X', mt_rand(0, 0xFFFFFF));
                    @endphp
                    <optgroup label="{{ $machine['pc_name'] }}">
                        <option value="{{ $machine['pc_name'] }}" style="color: {{ $color }}">
                            {{ $machine['pc_name'] }} - {{ $machine['data']['status'] }}
                        </option>
                    </optgroup>
                @endif
            @endforeach
        </select>
    </div>


    <!--
        ----------------------------------------------------------------
         1) 手機版：以卡片樣式顯示 (小螢幕 .d-block, md 以上隱藏)
        ----------------------------------------------------------------
    -->
    <div class="d-block d-md-none">
        <p class="text-info">（手機版卡片列表）</p>
        @foreach ($machines as $mIndex => $machine)
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">
                        {{ $machine['pc_name'] }}
                        <small class="float-right">
                            <span class="status-icon {{ $machine['data']['status'] }}"></span>
                            <span>{{ $machine['data']['status'] }}</span>
                        </small>
                    </h5>
                    <p class="card-text mb-1">
                        <strong>帳號狀態：</strong>
                        @foreach ($machine['rows'] as $status => $total)
                            {{ $status }}:{{ $total }}&nbsp;
                        @endforeach
                        @if (isset($machine['m_pro_gg_count']) && $machine['m_pro_gg_count'] > 6)
                            <span class="text-danger font-weight-bold">模擬器黑屏異常</span>
                        @endif
                    </p>
                    <p class="card-text mb-1">
                        <strong>模擬器：</strong>
                        {{ $machine['dnplayer_running'] }}/{{ $machine['dnplayer'] }}
                    </p>
                    <p class="card-text mb-1">
                        <strong>鑽石：</strong><br>
                        @foreach ($machine['money_rows'] as $server => $items)
                            <button class="btn btn-warning btn-sm mb-2"
                                    onclick="copyToClipboard('#mobile-data-{{ $machine['pc_name'] }}-{{ $server }}')">
                                {{ $server }}: {{ $items['total'] }}
                            </button>
                    <div id="mobile-data-{{ $machine['pc_name'] }}-{{ $server }}" style="display:none;">
                        {!! $items['rows'] !!}
                    </div>
                    <br>
                    @endforeach
                    </p>
                    <p class="card-text mb-1">
                        <strong>卡號到期：</strong> {{ $machine['card'] }} <br>
                        <strong>最後更新：</strong> {{ $machine['data']['last_updated'] }}
                    </p>
                    <p class="card-text">
                        <small>Pro: {{ $machine['data']['pro_version'] }} <br>
                            @if(isset($machine['data']['version']))
                                版本: {{ $machine['data']['version'] }}
                            @endif
                        </small>
                    </p>
                    <!-- 詳細資訊 & 更多指令 按鈕 -->
                    <div class="d-flex justify-content-between">
                        <button class="btn btn-info btn-sm"
                                data-toggle="modal"
                                data-target="#detailModal{{ $mIndex }}">
                            詳細
                        </button>

                        <!-- 更多指令：dropdown -->
                        <div class="dropdown">
                            <button class="btn btn-danger btn-sm dropdown-toggle"
                                    type="button"
                                    id="mobileDropdown{{ $mIndex }}"
                                    data-toggle="dropdown"
                                    aria-haspopup="true"
                                    aria-expanded="false">
                                指令
                            </button>
                            <div class="dropdown-menu command-dropdown-menu" aria-labelledby="mobileDropdown{{ $mIndex }}">
                                <button class="dropdown-item command-btn" data-token="{{ $token }}" data-mac="{{ $machine['mac'] }}" data-command="close_all_player">
                                    關閉所有模擬器
                                </button>
                                <button class="dropdown-item command-btn" data-token="{{ $token }}" data-mac="{{ $machine['mac'] }}" data-command="open_all_player">
                                    開啟所有模擬器
                                </button>
                                <button class="dropdown-item command-btn" data-token="{{ $token }}" data-mac="{{ $machine['mac'] }}" data-command="close_mpro">
                                    關閉大尾
                                </button>
                                <button class="dropdown-item command-btn" data-token="{{ $token }}" data-mac="{{ $machine['mac'] }}" data-command="open_mpro">
                                    開啟大尾
                                </button>
                                <button class="dropdown-item command-btn" data-token="{{ $token }}" data-mac="{{ $machine['mac'] }}" data-command="reopen_mpro">
                                    重開大尾
                                </button>
                                <div class="dropdown-divider"></div>
                                <button class="dropdown-item delete-btn text-danger" data-token="{{ $token }}" data-mac="{{ $machine['mac'] }}">
                                    重置網頁資料
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>


    <!--
        ----------------------------------------------------------------
         2) 桌面版：顯示完整表格 (md 以上 .d-block, 手機隱藏)
        ----------------------------------------------------------------
    -->
    <div class="d-none d-md-block">
        <p class="text-info">（桌面版表格）</p>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="thead-dark">
                <tr>
                    <th>主機 & 狀態</th>
                    <th>帳號狀態</th>
                    <th>模擬器數量</th>
                    <th>鑽石(點選可複製)</th>
                    <th>卡號到期</th>
                    <th>最後更新時間</th>
                    <th>遠端控制</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($machines as $index => $machine)
                    <tr>
                        <td>
                            <!-- 詳細資訊按鈕 (開啟 modal) -->
                            <button class="btn btn-info btn-sm mb-1" data-toggle="modal" data-target="#detailModal{{ $index }}">
                                {{ $machine['pc_name'] }}
                            </button>
                            <div>
                                <small>{{ $machine['data']['pro_version'] }}</small><br>
                                @if(isset($machine['data']['version']))
                                    <small>{{ $machine['data']['version'] }}</small>
                                @endif
                            </div>
                            <div class="mt-1">
                                <span class="status-icon {{ $machine['data']['status'] }}"></span>
                                <span>{{ $machine['data']['status'] }}</span>
                            </div>
                        </td>
                        <td>
                            @foreach ($machine['rows'] as $status => $total)
                                {{ $status }}:{{ $total }}<br>
                            @endforeach
                            @if (isset($machine['m_pro_gg_count']) && $machine['m_pro_gg_count'] > 6)
                                <span class="text-danger font-weight-bold">模擬器黑屏異常</span>
                            @endif
                        </td>
                        <td>
                            {{ $machine['dnplayer_running'] }}/{{ $machine['dnplayer'] }}
                        </td>
                        <td>
                            @foreach ($machine['money_rows'] as $server => $items)
                                <button class="btn btn-warning btn-sm mb-2"
                                        onclick="copyToClipboard('#server-data-{{ $machine['pc_name'] }}-{{ $server }}')">
                                    {{ $server }}: {{ $items['total'] }}
                                </button><br>
                                <!-- 隱藏要複製的資料 -->
                                <div id="server-data-{{ $machine['pc_name'] }}-{{ $server }}" style="display: none;">
                                    {!! $items['rows'] !!}
                                </div>
                            @endforeach
                        </td>
                        <td>{{ $machine['card'] }}</td>
                        <td>{{ $machine['data']['last_updated'] }}</td>
                        <td>
                            <!-- 更多指令：用 Bootstrap dropdown -->
                            <div class="dropdown">
                                <button class="btn btn-danger btn-sm dropdown-toggle"
                                        type="button"
                                        id="dropdownMenu{{ $index }}"
                                        data-toggle="dropdown"
                                        aria-haspopup="true"
                                        aria-expanded="false">
                                    更多指令 ▼
                                </button>
                                <div class="dropdown-menu command-dropdown-menu" aria-labelledby="dropdownMenu{{ $index }}">
                                    <button class="dropdown-item command-btn" data-token="{{ $token }}" data-mac="{{ $machine['mac'] }}" data-command="close_all_player">
                                        關閉所有模擬器
                                    </button>
                                    <button class="dropdown-item command-btn" data-token="{{ $token }}" data-mac="{{ $machine['mac'] }}" data-command="open_all_player">
                                        開啟所有模擬器
                                    </button>
                                    <button class="dropdown-item command-btn" data-token="{{ $token }}" data-mac="{{ $machine['mac'] }}" data-command="close_64_apk">
                                        關閉自動檢查64apk
                                    </button>
                                    <button class="dropdown-item command-btn" data-token="{{ $token }}" data-mac="{{ $machine['mac'] }}" data-command="open_64_apk">
                                        開啟自動檢查64apk
                                    </button>
                                    <button class="dropdown-item command-btn" data-token="{{ $token }}" data-mac="{{ $machine['mac'] }}" data-command="close_mpro">
                                        關閉大尾
                                    </button>
                                    <button class="dropdown-item command-btn" data-token="{{ $token }}" data-mac="{{ $machine['mac'] }}" data-command="open_mpro">
                                        開啟大尾
                                    </button>
                                    <button class="dropdown-item command-btn" data-token="{{ $token }}" data-mac="{{ $machine['mac'] }}" data-command="reopen_mpro">
                                        重開大尾
                                    </button>
                                    <button class="dropdown-item command-btn" data-token="{{ $token }}" data-mac="{{ $machine['mac'] }}" data-command="reboot_pc">
                                        重新開機
                                    </button>
                                    <button class="dropdown-item command-btn" data-token="{{ $token }}" data-mac="{{ $machine['mac'] }}" data-command="sort_player">
                                        排列模擬器
                                    </button>
                                    <button class="dropdown-item command-btn" data-token="{{ $token }}" data-mac="{{ $machine['mac'] }}" data-command="copy_to_local">
                                        雲端複製到本地
                                    </button>
                                    <button class="dropdown-item command-btn" data-token="{{ $token }}" data-mac="{{ $machine['mac'] }}" data-command="open_update_mpro">
                                        開啟自動更新
                                    </button>
                                    <button class="dropdown-item command-btn" data-token="{{ $token }}" data-mac="{{ $machine['mac'] }}" data-command="close_update_mpro">
                                        關閉自動更新
                                    </button>
                                    <button class="dropdown-item command-btn" data-token="{{ $token }}" data-mac="{{ $machine['mac'] }}" data-command="reopen_monitor">
                                        重開監視器程式
                                    </button>
                                    <button class="dropdown-item command-btn" data-token="{{ $token }}" data-mac="{{ $machine['mac'] }}" data-command="apk_install">
                                        安裝apk
                                    </button>
                                    <button class="dropdown-item command-btn" data-token="{{ $token }}" data-mac="{{ $machine['mac'] }}" data-command="open_exception_auto_reboot">
                                        開啟畫面異常自動重啟
                                    </button>
                                    <button class="dropdown-item command-btn" data-token="{{ $token }}" data-mac="{{ $machine['mac'] }}" data-command="close_exception_auto_reboot">
                                        關閉畫面異常自動重啟
                                    </button>
                                    <div class="dropdown-divider"></div>
                                    <button class="dropdown-item delete-btn text-danger" data-token="{{ $token }}" data-mac="{{ $machine['mac'] }}">
                                        重置網頁資料
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div> <!-- end container -->


<!-- 資料列表 Modal -->
<div class="modal fade" id="dataModal" tabindex="-1" aria-labelledby="dataModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="dataModalLabel">資料列表</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <ul>
                    @foreach ($merges as $key => $value)
                        <li>{{ $key }}: {{ $value }}</li>
                    @endforeach
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">關閉</button>
            </div>
        </div>
    </div>
</div>

<!-- 每台機器詳細資訊 Modal (手機 / 桌面都共用) -->
@foreach ($machines as $idx => $machine)
    <div class="modal fade" id="detailModal{{ $idx }}" tabindex="-1" aria-labelledby="detailModalLabel{{ $idx }}" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detailModalLabel{{ $idx }}">
                        詳細資訊 - {{ $machine['pc_name'] }}
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table class="table table-bordered table-sm">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>伺服器</th>
                            <th>狀態</th>
                            <th>鑽石數</th>
                            <th>格子數量</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($machine['role_list'] as $detailIndex => $detail)
                            <tr>
                                <td>{{ $detailIndex + 1 }}</td>
                                <td>{{ $detail[2] }}</td>
                                <td>{{ $detail[4] }}</td>
                                <td>{{ $detail[3] }}</td>
                                <td>{{ $detail[5] }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">關閉</button>
                </div>
            </div>
        </div>
    </div>
@endforeach


<!-- jQuery + Bootstrap JS (4.x) -->
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<!-- 需要 AJAX POST 或其他功能時，可以換更高版本 (3.5.1) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
<!--
若使用 Bootstrap 5：
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
-->

<script>
    // 複製
    function copyToClipboard(selector) {
        var text = $(selector).html().replace(/<br\s*[\/]?>/gi, '\n');
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

    // AJAX (單台)
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

    // AJAX (所有機器)
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
