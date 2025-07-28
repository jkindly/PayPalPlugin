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

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Sylius\PayPalPlugin\Model\Report;

final class ReportTest extends TestCase
{
    private Report $report;

    protected function setUp(): void
    {
        parent::setUp();
        $this->report = new Report('content', 'report.csv');
    }

    #[Test]
    public function it_has_content(): void
    {
        self::assertEquals('content', $this->report->content());
    }

    #[Test]
    public function it_has_a_file_name(): void
    {
        self::assertEquals('report.csv', $this->report->fileName());
    }
}
