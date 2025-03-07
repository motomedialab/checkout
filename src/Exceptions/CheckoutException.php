<?php

/**
 * @author MotoMediaLab <hello@motomedialab.com>
 * Created at: 27/07/2022
 */

namespace Motomedialab\Checkout\Exceptions;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;

class CheckoutException extends \Exception implements Responsable
{
    protected int $statusCode = 400;

    public function toResponse($request): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
        ], $this->statusCode);
    }

    public function setStatusCode(int $code): static
    {
        $this->statusCode = $code;

        return $this;
    }
}
