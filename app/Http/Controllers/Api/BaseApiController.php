<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

abstract class BaseApiController extends Controller
{
    use \App\Traits\ApiResponse;

    /**
     * API Version
     */
    protected string $version = 'v1';

    public function __construct()
    {
        // Maintain backward compatibility for $this->version usage if needed
        // but prefer $this->apiVersion from trait
        $this->apiVersion = $this->version;
    }
}
