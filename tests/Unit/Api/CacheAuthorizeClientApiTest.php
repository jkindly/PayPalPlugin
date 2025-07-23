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

namespace Tests\Sylius\PayPalPlugin\Unit\Api;

use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Payment\Model\GatewayConfigInterface;
use Sylius\PayPalPlugin\Api\AuthorizeClientApiInterface;
use Sylius\PayPalPlugin\Api\CacheAuthorizeClientApi;
use Sylius\PayPalPlugin\Api\CacheAuthorizeClientApiInterface;
use Sylius\PayPalPlugin\Entity\PayPalCredentialsInterface;
use Sylius\PayPalPlugin\Provider\UuidProviderInterface;

final class CacheAuthorizeClientApiTest extends TestCase
{
    private ObjectManager $payPalCredentialsManager;
    private ObjectRepository $payPalCredentialsRepository;
    private AuthorizeClientApiInterface $authorizeClientApi;
    private UuidProviderInterface $uuidProvider;
    private CacheAuthorizeClientApi $cacheAuthorizeClientApi;

    protected function setUp(): void
    {
        $this->payPalCredentialsManager = $this->createMock(ObjectManager::class);
        $this->payPalCredentialsRepository = $this->createMock(ObjectRepository::class);
        $this->authorizeClientApi = $this->createMock(AuthorizeClientApiInterface::class);
        $this->uuidProvider = $this->createMock(UuidProviderInterface::class);

        $this->cacheAuthorizeClientApi = new CacheAuthorizeClientApi(
            $this->payPalCredentialsManager,
            $this->payPalCredentialsRepository,
            $this->authorizeClientApi,
            $this->uuidProvider,
        );
    }

    public function testItImplementsCacheAuthorizeClientApiInterface(): void
    {
        $this->assertInstanceOf(CacheAuthorizeClientApiInterface::class, $this->cacheAuthorizeClientApi);
    }

    public function testItReturnsCachedAccessTokenIfItIsNotExpired(): void
    {
        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $payPalCredentials = $this->createMock(PayPalCredentialsInterface::class);

        $this->payPalCredentialsRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['paymentMethod' => $paymentMethod])
            ->willReturn($payPalCredentials);

        $payPalCredentials
            ->expects($this->once())
            ->method('isExpired')
            ->willReturn(false);

        $payPalCredentials
            ->expects($this->once())
            ->method('accessToken')
            ->willReturn('TOKEN');

        $result = $this->cacheAuthorizeClientApi->authorize($paymentMethod);

        $this->assertEquals('TOKEN', $result);
    }

    public function testItGetsAccessTokenFromApiCachesAndReturnsIt(): void
    {
        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $gatewayConfig = $this->createMock(GatewayConfigInterface::class);

        $this->payPalCredentialsRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['paymentMethod' => $paymentMethod])
            ->willReturn(null);

        $paymentMethod
            ->expects($this->once())
            ->method('getGatewayConfig')
            ->willReturn($gatewayConfig);

        $gatewayConfig
            ->expects($this->once())
            ->method('getConfig')
            ->willReturn(['client_id' => 'CLIENT_ID', 'client_secret' => '$ECRET']);

        $this->authorizeClientApi
            ->expects($this->once())
            ->method('authorize')
            ->with('CLIENT_ID', '$ECRET')
            ->willReturn('TOKEN');

        $this->uuidProvider
            ->expects($this->once())
            ->method('provide')
            ->willReturn('UUID');

        $this->payPalCredentialsManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->callback(function (PayPalCredentialsInterface $payPalCredentials) use ($paymentMethod): bool {
                return
                    $payPalCredentials->accessToken() === 'TOKEN' &&
                    $payPalCredentials->creationTime()->format('d-m-Y H:i') === (new \DateTime())->format('d-m-Y H:i') &&
                    $payPalCredentials->expirationTime()->format('d-m-Y H:i') === (new \DateTime())->modify('+3600 seconds')->format('d-m-Y H:i') &&
                    $payPalCredentials->paymentMethod() === $paymentMethod
                ;
            }));

        $this->payPalCredentialsManager
            ->expects($this->once())
            ->method('flush');

        $result = $this->cacheAuthorizeClientApi->authorize($paymentMethod);

        $this->assertEquals('TOKEN', $result);
    }

    public function testItReturnsExpiredTokenAndAskForANewOne(): void
    {
        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $gatewayConfig = $this->createMock(GatewayConfigInterface::class);
        $payPalCredentials = $this->createMock(PayPalCredentialsInterface::class);

        $this->payPalCredentialsRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['paymentMethod' => $paymentMethod])
            ->willReturn($payPalCredentials);

        $payPalCredentials
            ->method('isExpired')
            ->willReturn(true);

        $this->payPalCredentialsManager
            ->expects($this->once())
            ->method('remove')
            ->with($payPalCredentials);

        $this->payPalCredentialsManager
            ->expects($this->exactly(2))
            ->method('flush');

        $paymentMethod
            ->expects($this->once())
            ->method('getGatewayConfig')
            ->willReturn($gatewayConfig);

        $gatewayConfig
            ->expects($this->once())
            ->method('getConfig')
            ->willReturn(['client_id' => 'CLIENT_ID', 'client_secret' => '$ECRET']);

        $this->uuidProvider
            ->expects($this->once())
            ->method('provide')
            ->willReturn('UUID');

        $this->authorizeClientApi
            ->expects($this->once())
            ->method('authorize')
            ->with('CLIENT_ID', '$ECRET')
            ->willReturn('TOKEN');

        $this->payPalCredentialsManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->callback(function (PayPalCredentialsInterface $payPalCredentials) use ($paymentMethod): bool {
                return
                    $payPalCredentials->accessToken() === 'TOKEN' &&
                    $payPalCredentials->creationTime()->format('d-m-Y H:i') == (new \DateTime())->format('d-m-Y H:i') &&
                    $payPalCredentials->expirationTime()->format('d-m-Y H:i') == (new \DateTime())->modify('+3600 seconds')->format('d-m-Y H:i') &&
                    $payPalCredentials->paymentMethod() === $paymentMethod
                ;
            }));

        $result = $this->cacheAuthorizeClientApi->authorize($paymentMethod);

        $this->assertEquals('TOKEN', $result);
    }
}