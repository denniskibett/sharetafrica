<?php

namespace App\Integrations\IdReader;

use App\Integrations\Contracts\IdReader;

/** Where no reader is installed (the server has no tesseract yet): nothing is read, the customer types the details. */
class NullIdReader implements IdReader
{
    public function read(string $path, string $side): array
    {
        return [];
    }
}
