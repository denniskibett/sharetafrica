<?php

namespace Tests\Unit;

use App\Integrations\IdReader\KenyanIdText;
use Tests\TestCase;

class KenyanIdTextTest extends TestCase
{
    public function test_reads_the_front_of_a_kenyan_id(): void
    {
        $text = "JAMHURI YA KENYA REPUBLIC OF KENYA\nSERIAL NUMBER: 234567890 ID NUMBER: 12345678\nFULL NAMES\nJANE WANJIRU DOE\nDATE OF BIRTH\n05.03.1990\nSEX FEMALE\nDISTRICT OF BIRTH\nNAIROBI";
        $this->assertSame(['id_number' => '12345678', 'date_of_birth' => '1990-03-05', 'sex' => 'female', 'full_name' => 'Jane Wanjiru Doe'], KenyanIdText::parse($text));
    }

    public function test_reads_the_machine_lines_on_the_back(): void
    {
        $text = "some noise\nIDKYA2345678901<<1234<<<<<<<<<\n9003052F3101015KEN<<<<<<<<<<<6\nDOE<<JANE<WANJIRU<<<<<<<<<<<<<";
        $this->assertSame(['date_of_birth' => '1990-03-05', 'sex' => 'female', 'full_name' => 'Jane Wanjiru Doe'], KenyanIdText::parse($text));
    }

    public function test_returns_nothing_it_is_unsure_of(): void
    {
        $this->assertSame([], KenyanIdText::parse("blurry\n??"));
    }
}
