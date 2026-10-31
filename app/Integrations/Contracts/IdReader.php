<?php

namespace App\Integrations\Contracts;

/**
 * Reads the details off a photo of an ID so customers never type them (Kyc::storeDocument calls it for ID photos).
 * Today: Tesseract on this machine. Later: a KYC provider (Smile ID, IPRS lookup) behind the same contract.
 */
interface IdReader
{
    /**
     * @param  string  $side  id_front | id_back
     * @return array{full_name?: string, id_number?: string, date_of_birth?: string, sex?: string} only what was read
     */
    public function read(string $path, string $side): array;
}
