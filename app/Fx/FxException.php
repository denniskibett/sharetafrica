<?php

namespace App\Fx;

class FxException extends \RuntimeException
{
    public string $reason = 'fx_error';

    public static function make(string $reason, string $message): self
    {
        $e = new self($message);
        $e->reason = $reason;

        return $e;
    }
}
