<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class GuideBookPropertiesBridge extends Model implements Auditable
{
    use AuditableTrait;
    use RoomInfoParser;
    protected $fillable = ['guide_book_id', 'property_info_id', 'room_info_ids'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function guideBook(){
        return $this->belongsTo(GuideBook::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function property_info(){
        return $this->belongsTo(PropertyInfo::class);
    }

}
