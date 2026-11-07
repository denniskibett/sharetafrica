<?php

namespace App\Api;

use App\Support\ApiProblem;
use Illuminate\Http\JsonResponse;
use RuntimeException;

/** A refusal in the public API, rendered as application/problem+json (RFC 7807). */
final class ApiError extends RuntimeException
{
    public function __construct(public int $status, public string $type, public string $title, public string $detail, public array $extra = [])
    {
        parent::__construct($detail);
    }

    public function render(): JsonResponse
    {
        return ApiProblem::make($this->status, $this->type, $this->title, $this->detail, $this->extra);
    }
}
