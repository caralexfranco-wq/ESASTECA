<?php

namespace App\Models;

use App\Core\DB;
use PDO;

abstract class BaseModel
{
    protected static function db(): PDO
    {
        return DB::connection();
    }
}
