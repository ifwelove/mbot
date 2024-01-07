<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemSell extends Model
{
    use HasFactory;

    // 指定對應的資料表名稱
    protected $table = 'ItemSell';

    // 設定主鍵名稱 (預設是 'id'，但您的表結構中是 'index')
    protected $primaryKey = 'index';

    // 因為您使用了非預設名稱的自動增量主鍵，所以我們需要設定它的類型
    protected $keyType = 'int';

    // 指定是否自動維護時間戳 (created_at 和 updated_at)
    // 根據您的資料表結構，只有 Update_at，所以我們可以設定 $timestamps 屬性為 false
    public $timestamps = false;

    // 然後，我們可以自行定義 Update_at 的行為
    const UPDATED_AT = 'Update_at';

    // 設定可批量賦值的欄位
    protected $fillable = [
        'ItemVolume',
        'ItemCount',
        'ItemName',
        'ServerID',
        'TradeType',
        'Update_at',
        'ItemColor',
    ];
}
