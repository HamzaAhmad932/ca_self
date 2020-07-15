<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * Class PmsParent
 * @package App
 * @property $id
 * @property $logo
 * @property $name
 * @property $backend_name
 * @property $status
 * @property $page_configuration
 */
class PmsParent extends Model implements Auditable {

    use AuditableTrait;
    protected $fillable = ['logo', 'name', 'backend_name', 'status', 'page_configuration'];
}
