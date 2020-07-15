<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BookingSourceCapability extends Model
{
    /**
     * @property CaCapability cACapability
     */

    /**
     * @var array
     */
    protected $fillable = ['booking_source_form_id', 'ca_capability_id', 'status'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cACapability()
    {
        return $this->belongsTo('App\CaCapability', 'ca_capability_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function bookingSourceForm()
    {
        return $this->belongsTo('App\BookingSourceForm');
    }


}
