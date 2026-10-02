<?php

declare(strict_types=1);

final class PaymentSupervisionDecorator implements PaymentGateway
{
    public function __construct(
        private PaymentGateway $innerGateway,
        private string $gatewayName
    ) {
    }

    public function pay(float $amount): string
    {
        echo "SUPERVISION: Demande de paiement de {$amount} EUR via {$this->gatewayName}..." . PHP_EOL;
        $startTime = microtime(true);

        try {
            $transactionId = $this->innerGateway->pay($amount);
            
            $duration = round((microtime(true) - $startTime) * 1000);
            echo "SUPERVISION: Succès du paiement {$transactionId} en {$duration}ms." . PHP_EOL;
            
            return $transactionId;
        } catch (Throwable $e) {
            $duration = round((microtime(true) - $startTime) * 1000);
            echo "SUPERVISION: Échec du paiement après {$duration}ms. Erreur : {$e->getMessage()}" . PHP_EOL;
            throw $e;
        }
    }
}
