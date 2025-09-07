<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Responses\DefaultResponse;

class HealthCheckController extends Controller
{
    //TODO: comentario induz a erro pois o endpoint e GET e nao POST
    /**
     * Healthcheck
     *
     * POST api/healthcheck
     *
     * @return JsonResponse
     */
    public function healthCheck(): JsonResponse
    {
        return $this->response(new DefaultResponse());
    }
}
