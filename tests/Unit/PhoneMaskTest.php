<?php

namespace Tests\Unit;

use App\Support\PhoneMask;
use PHPUnit\Framework\TestCase;

class PhoneMaskTest extends TestCase
{
    public function test_formata_celular(): void
    {
        $this->assertSame('(47) 99999-8888', PhoneMask::format('47999998888'));
    }

    public function test_formata_fixo(): void
    {
        $this->assertSame('(11) 3333-4444', PhoneMask::format('1133334444'));
    }

    public function test_valida_telefone_com_ddd(): void
    {
        $this->assertTrue(PhoneMask::isValid('(47) 99999-8888'));
        $this->assertFalse(PhoneMask::isValid('99998888'));
    }
}