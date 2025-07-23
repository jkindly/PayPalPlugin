<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Sylius Sp. z o.o.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Tests\Sylius\PayPalPlugin\Unit\Model;

use PHPUnit\Framework\TestCase;
use Sylius\PayPalPlugin\Model\Report;

final class ReportTest extends TestCase
{
    private Report $report;

    protected function setUp(): void
    {
        $this->report = new Report('content', 'report.csv');
    }

    public function testItHasContent(): void
    {
        $this->assertEquals('content', $this->report->content());
    }

    public function testItHasAFileName(): void
    {
        $this->assertEquals('report.csv', $this->report->fileName());
    }
}