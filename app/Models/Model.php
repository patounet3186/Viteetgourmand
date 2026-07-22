<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

abstract class Model
{
    public function __construct(protected PDO $pdo)
    {
    }
}
