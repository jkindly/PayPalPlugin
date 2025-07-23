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

namespace Tests\Sylius\PayPalPlugin\Unit\Entity;

use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\PayPalPlugin\Entity\PayPalCredentials;
use Sylius\PayPalPlugin\Entity\PayPalCredentialsInterface;

final class PayPalCredentialsTest extends TestCase
{
    private PaymentMethodInterface $paymentMethod;
    private PayPalCredentials $payPalCredentials;

    protected function setUp(): void
    {
        $this->paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $this->payPalCredentials = new PayPalCredentials(
            '123ASD123',
            $this->paymentMethod,
            'TOKEN',
            new \DateTime('2020-01-01 10:00:00'),
            3600
        );
    }

    public function testItImplementsPaypalCredentialsInterface(): void
    {
        $this->assertInstanceOf(PayPalCredentialsInterface::class, $this->payPalCredentials);
    }

    public function testItHasAPaymentMethod(): void
    {
        $result = $this->payPalCredentials->paymentMethod();

        $this->assertEquals($this->paymentMethod, $result);
    }

    public function testItHasAAccessToken(): void
    {
        $result = $this->payPalCredentials->accessToken();

        $this->assertEquals('TOKEN', $result);
    }

    public function testItHasACreationTime(): void
    {
        $result = $this->payPalCredentials->creationTime();

        $this->assertEquals(new \DateTime('2020-01-01 10:00:00'), $result);
    }

    public function testItHasAExpirationTime(): void
    {
        $result = $this->payPalCredentials->expirationTime();

        $this->assertEquals(new \DateTime('2020-01-01 11:00:00'), $result);
    }

    public function testItCanBeExpired(): void
    {
        $result = $this->payPalCredentials->isExpired();

        $this->assertTrue($result);
    }
}