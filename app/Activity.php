<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Activity extends Model
{

    protected $fillable = [
        'name','desc'
    ];

    /**
     * This Function is not useAle now this table is replaced with user_preferences_notification_settings()
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */

    public function user_notification_setting()
    {
        return $this->hasMany('App\UserNotificationSetting');
    }


    public function user_preferences_notification_settings()
    {
        return $this->hasMany('App\UserPreferencesNotificationSettings');
    }

}
