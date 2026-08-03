<?php

namespace App\Providers;

use App\Contracts\AuditLogger;
use App\Services\Audit\DatabaseAuditLogger;
use Illuminate\Support\ServiceProvider;

class AuditServiceProvider extends ServiceProvider
{
    public array $bindings = [AuditLogger::class => DatabaseAuditLogger::class,];
}
