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

namespace Sylius\PayPalPlugin\Repository\Query;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Repository\PaymentRepositoryInterface;
use Sylius\PayPalPlugin\DependencyInjection\SyliusPayPalExtension;

final class PaypalPaymentQuery implements PaypalPaymentQueryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private PaymentRepositoryInterface $paymentRepository,
        private array $updatableStates = ['cart', 'new', 'processing'],
        private array $cancellableStates = ['cart', 'new', 'processing', 'completed'],
        private array $refundableStates = ['completed'],
    ) {
    }

    public function getForUpdateByOrderId(string $paypalOrderId): ?PaymentInterface
    {
        $queryBuilder = $this->getPaypalPaymentQueryBuilder('o')
            ->andWhere('o.state IN (:states)')
            ->setParameter('states', $this->updatableStates)
            ->addOrderBy('o.createdAt', 'DESC')
        ;

        return $this->doGetPayment($queryBuilder, $paypalOrderId);
    }

    public function getForCancellationByOrderId(string $paypalOrderId): ?PaymentInterface
    {
        $queryBuilder = $this->getPaypalPaymentQueryBuilder('o')
            ->andWhere('o.state IN (:states)')
            ->setParameter('states', $this->cancellableStates)
            ->addOrderBy('o.createdAt', 'DESC')
        ;

        return $this->doGetPayment($queryBuilder, $paypalOrderId);
    }

    public function getForRefundingByOrderId(string $paypalOrderId): ?PaymentInterface
    {
        $queryBuilder = $this->getPaypalPaymentQueryBuilder('o')
            ->andWhere('o.state IN (:states)')
            ->setParameter('states', $this->refundableStates)
            ->addOrderBy('o.updatedAt', 'DESC')
        ;

        return $this->doGetPayment($queryBuilder, $paypalOrderId);
    }

    private function doGetPayment(QueryBuilder $queryBuilder, string $paypalOrderId): ?PaymentInterface
    {
        if ($this->isCastAvailable()) {
            return $queryBuilder
                ->andWhere('CAST(o.details AS text) LIKE :orderId')
                ->setParameter('orderId', '%"paypal_order_id":"' . $paypalOrderId . '"%')
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult()
            ;
        }

        $payments = $queryBuilder
            ->getQuery()
            ->toIterable()
        ;

        foreach ($payments as $payment) {
            $details = $payment->getDetails();
            if (isset($details['paypal_order_id']) && $details['paypal_order_id'] === $paypalOrderId) {
                return $payment;
            }
        }

        return null;
    }

    private function getPaypalPaymentQueryBuilder(string $alias): QueryBuilder
    {
        return $this->paymentRepository
            ->createQueryBuilder($alias)
            ->innerJoin($alias . '.method', 'method')
            ->innerJoin('method.gatewayConfig', 'gatewayConfig')
            ->andWhere('gatewayConfig.factoryName = :factoryName')
            ->setParameter('factoryName', SyliusPayPalExtension::PAYPAL_FACTORY_NAME)
        ;
    }

    private function isCastAvailable(): bool
    {
        return null !== $this->entityManager->getConfiguration()->getCustomStringFunction('CAST');
    }
}
