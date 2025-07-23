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

namespace Tests\Sylius\PayPalPlugin\Unit\Provider;

use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Addressing\Model\CountryInterface;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\PayPalPlugin\Provider\AvailableCountriesProvider;

final class AvailableCountriesProviderTest extends TestCase
{
    private RepositoryInterface&MockObject $countryRepository;
    private ChannelContextInterface&MockObject $channelContext;
    private AvailableCountriesProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->countryRepository = $this->createMock(RepositoryInterface::class);
        $this->channelContext = $this->createMock(ChannelContextInterface::class);

        $this->provider = new AvailableCountriesProvider(
            $this->countryRepository,
            $this->channelContext
        );
    }

    public function testProvidesAvailableCountriesIfChannelDoesNotHaveAny(): void
    {
        $countryOne = $this->createMock(CountryInterface::class);
        $countryTwo = $this->createMock(CountryInterface::class);
        $countryThree = $this->createMock(CountryInterface::class);
        $channel = $this->createMock(ChannelInterface::class);
        $collection = $this->createMock(Collection::class);

        $channel->method('getCountries')->willReturn($collection);
        $collection->method('toArray')->willReturn([]);
        $this->channelContext->method('getChannel')->willReturn($channel);

        $countryOne->method('getCode')->willReturn('PL');
        $countryTwo->method('getCode')->willReturn('US');
        $countryThree->method('getCode')->willReturn('RU');
        $this->countryRepository->method('findBy')->with(['enabled' => true])->willReturn([$countryOne, $countryTwo, $countryThree]);

        $result = $this->provider->provide();

        self::assertEquals(['PL', 'US', 'RU'], $result);
    }

    public function testProvidesAvailableCountriesIfChannelContainsCountries(): void
    {
        $countryOne = $this->createMock(CountryInterface::class);
        $countryTwo = $this->createMock(CountryInterface::class);
        $channel = $this->createMock(ChannelInterface::class);
        $collection = $this->createMock(Collection::class);

        $channel->method('getCountries')->willReturn($collection);
        $collection->method('toArray')->willReturn([$countryOne, $countryTwo]);
        $this->channelContext->method('getChannel')->willReturn($channel);

        $countryOne->method('getCode')->willReturn('DE');
        $countryTwo->method('getCode')->willReturn('CN');
        $this->countryRepository->expects($this->never())->method('findBy');

        $result = $this->provider->provide();

        self::assertEquals(['DE', 'CN'], $result);
    }
}
