<?php
declare(strict_types=1);

use EonX\EasyServerless\Health\Controller\HealthCheckController;
use Illuminate\Support\Facades\Route;

Route::get('/easy-serverless/health-check', HealthCheckController::class);
