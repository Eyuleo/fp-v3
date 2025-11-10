<?php

use App\Jobs\AutoApproveOrdersJob;
use App\Jobs\AutoCancelPendingOrdersJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule automated order management jobs
Schedule::job(new AutoApproveOrdersJob)->daily()->at('00:00');
Schedule::job(new AutoCancelPendingOrdersJob)->hourly();
