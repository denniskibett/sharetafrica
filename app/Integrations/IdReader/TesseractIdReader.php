<?php

namespace App\Integrations\IdReader;

use App\Integrations\Contracts\IdReader;
use Illuminate\Support\Facades\Process;

/** OCR with the tesseract binary (config services.tesseract.path), then KenyanIdText pulls out the fields. */
class TesseractIdReader implements IdReader
{
    public function read(string $path, string $side): array
    {
        $result = Process::timeout(30)->run([config('services.tesseract.path'), $path, 'stdout', '-l', 'eng', '--psm', '3']);
        if (! $result->successful()) {
            report(new \RuntimeException('tesseract failed: '.$result->errorOutput()));

            return [];
        }

        return KenyanIdText::parse($result->output());
    }

    public static function available(): bool
    {
        return Process::run([config('services.tesseract.path'), '--version'])->successful();
    }
}
