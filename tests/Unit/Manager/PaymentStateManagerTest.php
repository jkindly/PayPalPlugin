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

use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Abstraction\StateMachine\StateMachineInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Payment\PaymentTransitions;
use Sylius\PayPalPlugin\Manager\PaymentStateManager;
use Sylius\PayPalPlugin\Manager\PaymentStateManagerInterface;
use Sylius\PayPalPlugin\Payum\Action\StatusAction;
use Sylius\PayPalPlugin\Processor\PaymentCompleteProcessorInterface;

final class PaymentStateManagerTest extends TestCase
{
    private StateMachineInterface&MockObject $stateMachine;
    private ObjectManager&MockObject $paymentManager;
    private PaymentCompleteProcessorInterface&MockObject $paymentCompleteProcessor;
    private PaymentStateManager $paymentStateManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->stateMachine = $this->createMock(StateMachineInterface::class);
        $this->paymentManager = $this->createMock(ObjectManager::class);
        $this->paymentCompleteProcessor = $this->createMock(PaymentCompleteProcessorInterface::class);

        $this->paymentStateManager = new PaymentStateManager(
            $this->stateMachine,
            $this->paymentManager,
            $this->paymentCompleteProcessor,
        );
    }

    public function testItImplementsPaymentStateManagerInterface(): void
    {
        self::assertInstanceOf(PaymentStateManagerInterface::class, $this->paymentStateManager);
    }

    public function testItCreatesPayment(): void
    {
        $payment = $this->createMock(PaymentInterface::class);

        $this->stateMachine
            ->expects(self::once())
            ->method('apply')
            ->with($payment, PaymentTransitions::GRAPH, PaymentTransitions::TRANSITION_CREATE);

        $this->paymentManager
            ->expects(self::once())
            ->method('flush');

        $this->paymentStateManager->create($payment);
    }

    public function testItCompletesPaymentIfItsCompletedInPaypal(): void
    {
        $payment = $this->createMock(PaymentInterface::class);

        $this->paymentCompleteProcessor
            ->expects(self::once())
            ->method('completePayment')
            ->with($payment);

        $payment
            ->expects(self::once())
            ->method('getDetails')
            ->willReturn(['status' => StatusAction::STATUS_COMPLETED]);

        $this->stateMachine
            ->expects(self::once())
            ->method('apply')
            ->with($payment, PaymentTransitions::GRAPH, PaymentTransitions::TRANSITION_COMPLETE);

        $this->paymentManager
            ->expects(self::once())
            ->method('flush');

        $this->paymentStateManager->complete($payment);
    }

    public function testItProcessesPaymentIfItsProcessingInPaypalAndNotProcessingInSyliusYet(): void
    {
        $payment = $this->createMock(PaymentInterface::class);

        $this->paymentCompleteProcessor
            ->expects(self::once())
            ->method('completePayment')
            ->with($payment);

        $payment
            ->expects(self::once())
            ->method('getDetails')
            ->willReturn(['status' => StatusAction::STATUS_PROCESSING]);

        $payment
            ->expects(self::once())
            ->method('getState')
            ->willReturn(PaymentInterface::STATE_NEW);

        $this->stateMachine
            ->expects(self::once())
            ->method('apply')
            ->with($payment, PaymentTransitions::GRAPH, PaymentTransitions::TRANSITION_PROCESS);

        $this->paymentManager
            ->expects(self::once())
            ->method('flush');

        $this->paymentStateManager->complete($payment);
    }

    public function testItDoesNothingIfPaymentIsProcessingInPaypalButAlreadyProcessingInSylius(): void
    {
        $payment = $this->createMock(PaymentInterface::class);

        $this->paymentCompleteProcessor
            ->expects(self::once())
            ->method('completePayment')
            ->with($payment);

        $payment
            ->expects(self::once())
            ->method('getDetails')
            ->willReturn(['status' => StatusAction::STATUS_PROCESSING]);

        $payment
            ->expects(self::once())
            ->method('getState')
            ->willReturn(PaymentInterface::STATE_PROCESSING);

        $this->stateMachine
            ->expects($this->never())
            ->method('apply');

        $this->paymentStateManager->complete($payment);
    }

    public function testItProcessesPayment(): void
    {
        $payment = $this->createMock(PaymentInterface::class);

        $this->stateMachine
            ->expects(self::once())
            ->method('apply')
            ->with($payment, PaymentTransitions::GRAPH, PaymentTransitions::TRANSITION_PROCESS);

        $this->paymentManager
            ->expects(self::once())
            ->method('flush');

        $this->paymentStateManager->process($payment);
    }

    public function testItCancelsPayment(): void
    {
        $payment = $this->createMock(PaymentInterface::class);

        $this->stateMachine
            ->expects(self::once())
            ->method('apply')
            ->with($payment, PaymentTransitions::GRAPH, PaymentTransitions::TRANSITION_CANCEL);

        $this->paymentManager
            ->expects(self::once())
            ->method('flush');

        $this->paymentStateManager->cancel($payment);
    }
}
