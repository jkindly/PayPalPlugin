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

namespace Sylius\PayPalPlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Sylius\Bundle\CoreBundle\Doctrine\Migrations\AbstractMigration;

final class Version20241206094355 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update factory name for PayPal gateway';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE sylius_gateway_config SET factory_name = 'sylius_paypal' WHERE factory_name = 'sylius.pay_pal'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("UPDATE sylius_gateway_config SET factory_name = 'sylius.pay_pal' WHERE factory_name = 'sylius_paypal'");
    }
}
