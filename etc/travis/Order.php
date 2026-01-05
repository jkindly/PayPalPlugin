<?php

declare(strict_types=1);

namespace Sylius\PayPalPlugin\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Order as BaseOrder;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_order')]
final class Order extends BaseOrder
{
    #[ORM\Column(type: 'string', nullable: true)]
    private string $test;
}
