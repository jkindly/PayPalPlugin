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

namespace spec\Sylius\PayPalPlugin\Manager;

use Doctrine\Persistence\ObjectManager;
use PhpSpec\ObjectBehavior;
use Sylius\Abstraction\StateMachine\StateMachineInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Payment\PaymentTransitions;
use Sylius\PayPalPlugin\Manager\PaymentStateManagerInterface;
use Sylius\PayPalPlugin\Payum\Action\StatusAction;
use Sylius\PayPalPlugin\Processor\PaymentCompleteProcessorInterface;

final class PaymentStateManagerSpec extends ObjectBehavior
{
    function let(
        StateMachineInterface $stateMachine,
        ObjectManager $paymentManager,
        PaymentCompleteProcessorInterface $paymentCompleteProcessor,
    ): void {
        $this->beConstructedWith($stateMachine, $paymentManager, $paymentCompleteProcessor);
    }

    function it_implements_payment_state_manager_interface(): void
    {
        $this->shouldImplement(PaymentStateManagerInterface::class);
    }

    function it_creates_payment(
        StateMachineInterface $stateMachine,
        ObjectManager $paymentManager,
        PaymentInterface $payment,
    ): void {
        $stateMachine->apply($payment, PaymentTransitions::GRAPH, PaymentTransitions::TRANSITION_CREATE)->shouldBeCalled();
        $paymentManager->flush()->shouldBeCalled();

        $this->create($payment);
    }

    function it_completes_payment_if_its_completed_in_paypal(
        StateMachineInterface $stateMachine,
        ObjectManager $paymentManager,
        PaymentCompleteProcessorInterface $paymentCompleteProcessor,
        PaymentInterface $payment,
    ): void {
        $paymentCompleteProcessor->completePayment($payment);
        $payment->getDetails()->willReturn(['status' => StatusAction::STATUS_COMPLETED]);

        $stateMachine->apply($payment, PaymentTransitions::GRAPH, PaymentTransitions::TRANSITION_COMPLETE)->shouldBeCalled();
        $paymentManager->flush()->shouldBeCalled();

        $this->complete($payment);
    }

    function it_processes_payment_if_its_processing_in_paypal_and_not_processing_in_sylius_yet(
        StateMachineInterface $stateMachine,
        ObjectManager $paymentManager,
        PaymentCompleteProcessorInterface $paymentCompleteProcessor,
        PaymentInterface $payment,
    ): void {
        $paymentCompleteProcessor->completePayment($payment);
        $payment->getDetails()->willReturn(['status' => StatusAction::STATUS_PROCESSING]);
        $payment->getState()->willReturn(PaymentInterface::STATE_NEW);

        $stateMachine->apply($payment, PaymentTransitions::GRAPH, PaymentTransitions::TRANSITION_PROCESS)->shouldBeCalled();
        $paymentManager->flush()->shouldBeCalled();

        $this->complete($payment);
    }

    function it_does_nothing_if_payment_is_processing_in_paypal_but_already_processing_in_sylius(
        StateMachineInterface $stateMachine,
        PaymentCompleteProcessorInterface $paymentCompleteProcessor,
        PaymentInterface $payment,
    ): void {
        $paymentCompleteProcessor->completePayment($payment);
        $payment->getDetails()->willReturn(['status' => StatusAction::STATUS_PROCESSING]);
        $payment->getState()->willReturn(PaymentInterface::STATE_PROCESSING);

        $stateMachine->apply($payment, PaymentTransitions::GRAPH, PaymentTransitions::TRANSITION_COMPLETE)->shouldNotBeCalled();
        $stateMachine->apply($payment, PaymentTransitions::GRAPH, PaymentTransitions::TRANSITION_PROCESS)->shouldNotBeCalled();

        $this->complete($payment);
    }

    function it_processes_payment(
        StateMachineInterface $stateMachine,
        ObjectManager $paymentManager,
        PaymentInterface $payment,
    ): void {
        $stateMachine->apply($payment, PaymentTransitions::GRAPH, PaymentTransitions::TRANSITION_PROCESS)->shouldBeCalled();
        $paymentManager->flush()->shouldBeCalled();

        $this->process($payment);
    }

    function it_cancels_payment(
        StateMachineInterface $stateMachine,
        ObjectManager $paymentManager,
        PaymentInterface $payment,
    ): void {
        $stateMachine->apply($payment, PaymentTransitions::GRAPH, PaymentTransitions::TRANSITION_CANCEL)->shouldBeCalled();
        $paymentManager->flush()->shouldBeCalled();

        $this->cancel($payment);
    }
}
