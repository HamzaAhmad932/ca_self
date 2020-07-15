<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

/**
 * @property string image
 * @property mixed type
 * @property int status
 * @property mixed booking_id
 * @property mixed created_at
 */
class GuestImage extends Model implements Auditable
{
	use AuditableTrait;

	const STATUS_ACCEPTED = 1;
	const STATUS_REJECTED = 2;
	const STATUS_PENDING = 0;
    protected $fillable = ['booking_id' , 'image'  , 'status', 'type', 'description', 'created_at'];

    const TYPE_PASSPORT = 'passport';
    const TYPE_CREDIT_CARD = 'credit_card';
    const TYPE_SIGNATURE = 'signature';
    const TYPE_SELFIE = 'selfie';

    const TOTAL_ALLOWED_IMAGES = 7;

    const PATH_IMAGES = 'storage/uploads/guestImages/';

    public $appends = ['document_rejected_description'];

    public static function isSupportedType($type) {
        return in_array($type,
            [
                self::TYPE_PASSPORT,
                self::TYPE_CREDIT_CARD,
                self::TYPE_SIGNATURE,
                self::TYPE_SELFIE
            ]);
    }

    public function booking_info(){
        return $this->belongsTo(BookingInfo::class, 'booking_id');
    }
    public function getDocumentRejectedDescriptionAttribute(){

        if(!empty($this->description)){
            return '('.handleSpecialCharacters($this->description).')';
        }
        return '';
    }

    public function getReadableTypeAttribute(){

        $type_arr = explode('_', $this->type);
        $readable_type = '';
        foreach ($type_arr as $key => $type){
            $readable_type .= $key == 0 ? ucfirst($type) : ' '.$type;
        }
        return $readable_type;
    }

}
