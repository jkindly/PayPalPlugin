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

namespace Tests\Sylius\PayPalPlugin\Unit\Manager;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Sylius\PayPalPlugin\Manager\PayPalCredentialsManager;

final class PayPalCredentialsManagerTest extends TestCase
{
    private PayPalCredentialsManager $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = new PayPalCredentialsManager();
    }

    #[Test]
    public function it_stores_credentials_on_a_brand_new_config_without_snapshotting_anything(): void
    {
        $config = $this->manager->store(
            ['use_authorize' => 1, 'reports_sftp_password' => null, 'reports_sftp_username' => null],
            false,
            ['client_id' => 'PROD-ID', 'client_secret' => 'PROD-SECRET'],
        );

        self::assertSame('PROD-ID', $config['client_id']);
        self::assertSame('PROD-SECRET', $config['client_secret']);
        self::assertFalse($config['sandbox']);
        self::assertSame(['client_id' => 'PROD-ID', 'client_secret' => 'PROD-SECRET'], $config['production_credentials']);
        self::assertArrayNotHasKey('sandbox_credentials', $config);
    }

    #[Test]
    public function it_preserves_pre_existing_production_credentials_when_setting_up_sandbox_on_a_legacy_config(): void
    {
        // A legacy config has no "sandbox" key yet, only flat, real production credentials, exactly like
        // any PayPal payment method created before mode-switching existed.
        $legacyConfig = [
            'client_id' => 'REAL-PROD-ID',
            'client_secret' => 'REAL-PROD-SECRET',
            'merchant_id' => 'REAL-MERCHANT-ID',
            'use_authorize' => '1',
            'reports_sftp_password' => null,
            'reports_sftp_username' => null,
        ];

        $config = $this->manager->store(
            $legacyConfig,
            true,
            ['client_id' => 'TEST-SANDBOX-ID', 'client_secret' => 'TEST-SANDBOX-SECRET'],
        );

        self::assertSame('TEST-SANDBOX-ID', $config['client_id']);
        self::assertSame('TEST-SANDBOX-SECRET', $config['client_secret']);
        self::assertTrue($config['sandbox']);

        self::assertSame(
            ['client_id' => 'REAL-PROD-ID', 'client_secret' => 'REAL-PROD-SECRET', 'merchant_id' => 'REAL-MERCHANT-ID'],
            $config['production_credentials'],
        );
    }

    #[Test]
    public function it_restores_the_preserved_production_credentials_when_switching_back(): void
    {
        $legacyConfig = [
            'client_id' => 'REAL-PROD-ID',
            'client_secret' => 'REAL-PROD-SECRET',
            'use_authorize' => '1',
            'reports_sftp_password' => null,
            'reports_sftp_username' => null,
        ];

        $config = $this->manager->store(
            $legacyConfig,
            true,
            ['client_id' => 'TEST-SANDBOX-ID', 'client_secret' => 'TEST-SANDBOX-SECRET'],
        );

        $config = $this->manager->switchTo($config, false);

        self::assertSame('REAL-PROD-ID', $config['client_id']);
        self::assertSame('REAL-PROD-SECRET', $config['client_secret']);
        self::assertFalse($config['sandbox']);
    }

    #[Test]
    public function it_reports_no_credentials_for_a_mode_that_was_never_configured(): void
    {
        $config = $this->manager->store([], false, ['client_id' => 'PROD-ID', 'client_secret' => 'PROD-SECRET']);

        self::assertTrue($this->manager->hasCredentials($config, false));
        self::assertFalse($this->manager->hasCredentials($config, true));
    }
}
