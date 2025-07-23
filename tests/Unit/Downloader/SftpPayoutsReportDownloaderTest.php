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

namespace Tests\Sylius\PayPalPlugin\Unit\Downloader;

use phpseclib3\Net\SFTP;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Payment\Model\GatewayConfigInterface;
use Sylius\PayPalPlugin\Downloader\PayoutsReportDownloaderInterface;
use Sylius\PayPalPlugin\Downloader\SftpPayoutsReportDownloader;
use Sylius\PayPalPlugin\Exception\PayPalReportDownloadException;
use Sylius\PayPalPlugin\Model\Report;

final class SftpPayoutsReportDownloaderTest extends TestCase
{
    private SFTP&MockObject $sftp;

    private SftpPayoutsReportDownloader $sftpPayoutsReportDownloader;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sftp = $this->createMock(SFTP::class);
        $this->sftpPayoutsReportDownloader = new SftpPayoutsReportDownloader($this->sftp);
    }

    public function testItImplementsPayoutsReportDownloaderInterface(): void
    {
        self::assertInstanceOf(PayoutsReportDownloaderInterface::class, $this->sftpPayoutsReportDownloader);
    }

    public function testItReturnsContentOfTheLatestPytReportFromPaypalSftpServer(): void
    {
        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $gatewayConfig = $this->createMock(GatewayConfigInterface::class);

        $paymentMethod
            ->expects(self::once())
            ->method('getGatewayConfig')
            ->willReturn($gatewayConfig);

        $gatewayConfig
            ->expects(self::once())
            ->method('getConfig')
            ->willReturn([
                'partner_attribution_id' => 'PARTNER-ID',
                'reports_sftp_username' => 'SFTP-USERNAME',
                'reports_sftp_password' => 'SFTP-PASSWORD',
            ]);

        $this->sftp
            ->expects(self::once())
            ->method('login')
            ->with('SFTP-USERNAME', 'SFTP-PASSWORD')
            ->willReturn(true);

        $yesterday = new \DateTime('-1 day');
        $this->sftp
            ->expects(self::once())
            ->method('get')
            ->with(sprintf('ppreports/outgoing/PYT.%s.PARTNER-ID.R.0.2.0.CSV', $yesterday->format('Ymd')))
            ->willReturn('REPORT-CONTENT');

        $result = $this->sftpPayoutsReportDownloader->downloadFor($yesterday, $paymentMethod);
        $expected = new Report('REPORT-CONTENT', sprintf('PYT%s.csv', $yesterday->format('Ymd')));

        self::assertEquals($expected, $result);
    }

    public function testItThrowsAnExceptionIfPaymentMethodHasNoPartnerAttributionId(): void
    {
        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $gatewayConfig = $this->createMock(GatewayConfigInterface::class);

        $paymentMethod
            ->expects(self::once())
            ->method('getGatewayConfig')
            ->willReturn($gatewayConfig);

        $gatewayConfig
            ->expects(self::once())
            ->method('getConfig')
            ->willReturn([]);

        $this->expectException(PayPalReportDownloadException::class);

        $this->sftpPayoutsReportDownloader->downloadFor(new \DateTime(), $paymentMethod);
    }

    public function testItThrowsAnExceptionIfCredentialsAreInvalid(): void
    {
        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $gatewayConfig = $this->createMock(GatewayConfigInterface::class);

        $paymentMethod
            ->expects(self::once())
            ->method('getGatewayConfig')
            ->willReturn($gatewayConfig);

        $gatewayConfig
            ->expects(self::once())
            ->method('getConfig')
            ->willReturn([
                'partner_attribution_id' => 'PARTNER-ID',
                'reports_sftp_username' => 'SFTP-USERNAME',
                'reports_sftp_password' => 'SFTP-PASSWORD',
            ]);

        $this->sftp
            ->expects(self::once())
            ->method('login')
            ->with('SFTP-USERNAME', 'SFTP-PASSWORD')
            ->willReturn(false);

        $this->expectException(PayPalReportDownloadException::class);

        $this->sftpPayoutsReportDownloader->downloadFor(new \DateTime(), $paymentMethod);
    }

    public function testItThrowsAnExceptionIfThereIsNoReportWithGivenName(): void
    {
        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $gatewayConfig = $this->createMock(GatewayConfigInterface::class);

        $paymentMethod
            ->expects(self::once())
            ->method('getGatewayConfig')
            ->willReturn($gatewayConfig);

        $gatewayConfig
            ->expects(self::once())
            ->method('getConfig')
            ->willReturn([
                'partner_attribution_id' => 'PARTNER-ID',
                'reports_sftp_username' => 'SFTP-USERNAME',
                'reports_sftp_password' => 'SFTP-PASSWORD',
            ]);

        $this->sftp
            ->expects(self::once())
            ->method('login')
            ->with('SFTP-USERNAME', 'SFTP-PASSWORD')
            ->willReturn(true);

        $yesterday = new \DateTime('-1 day');
        $this->sftp
            ->expects(self::once())
            ->method('get')
            ->with(sprintf('ppreports/outgoing/PYT.%s.PARTNER-ID.R.0.2.0.CSV', $yesterday->format('Ymd')))
            ->willReturn(false);

        $this->expectException(PayPalReportDownloadException::class);

        $this->sftpPayoutsReportDownloader->downloadFor($yesterday, $paymentMethod);
    }
}
