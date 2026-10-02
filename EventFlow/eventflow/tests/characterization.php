<?php

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/TestRunner.php';

$tests = new TestRunner();

function createBooking(
    string $customerType = 'standard',
    string $passType = 'day',
    float $price = 50.0,
    int $quantity = 1,
    ?string $phone = '0600000000'
): Booking {
    $customer = new Customer(1, new Email('test@example.com'), $phone, $customerType);
    $ticket = new Ticket('TEST', 'Ticket test', $price);
    $booking = new Booking(1, $customer, $passType);
    $booking->addItem(new BookingItem($ticket, $quantity));
    return $booking;
}

// fonction pour appeler confirm sans afficher les echo en console
function confirmBooking(BookingService $service, Booking $booking, string $payment = 'stripe'): float
{
    ob_start();
    $total = $service->confirm($booking, $payment);
    ob_end_clean();
    return $total;
}

$service = new BookingService();

// Tests de base
$standard = createBooking('standard', 'day', 50.0, 2);
$standardTotal = confirmBooking($service, $standard, 'stripe');
$tests->near(100.0, $standardTotal, 'standard customer keeps initial total');
$tests->same('confirmed', $standard->status, 'booking becomes confirmed');

$vip = createBooking('vip', 'day', 50.0, 2);
$vipTotal = confirmBooking($service, $vip, 'stripe');
$tests->near(90.0, $vipTotal, 'legacy VIP rule gives 10 percent discount');

$threeDays = createBooking('standard', '3days', 60.0, 2);
$threeDaysTotal = confirmBooking($service, $threeDays, 'stripe');
$tests->near(100.0, $threeDaysTotal, 'three day pass discount is 20 euros (ticket #102)');

// Cumul VIP et pass 3 jours : (120 * 0.9) - 20 = 88.0
$vipThreeDays = createBooking('vip', '3days', 60.0, 2);
$vipThreeDaysTotal = confirmBooking($service, $vipThreeDays, 'stripe');
$tests->near(88.0, $vipThreeDaysTotal, 'cumulative discount: VIP 10% then 3days pass -20 euros (ticket #102)');

// Panier avec plusieurs articles : 2 x 50 + 1 x 30 = 130.0
$multi = new Booking(2, new Customer(2, new Email('multi@example.com')), 'day');
$multi->addItem(new BookingItem(new Ticket('T1', 'Ticket 1', 50.0), 2));
$multi->addItem(new BookingItem(new Ticket('T2', 'Ticket 2', 30.0), 1));
$multiTotal = confirmBooking($service, $multi, 'stripe');
$tests->near(130.0, $multiTotal, 'multiple items total is calculated correctly');

// Client sans telephone
$noPhone = new Booking(3, new Customer(3, new Email('nophone@example.com'), null), 'day');
$noPhone->addItem(new BookingItem(new Ticket('T1', 'Ticket 1', 40.0), 1));
$noPhoneTotal = confirmBooking($service, $noPhone, 'stripe');
$tests->near(40.0, $noPhoneTotal, 'booking succeeds when customer phone is null');

// Tests d'erreurs :D
try {
    ob_start();
    $empty = new Booking(4, new Customer(4, new Email('valid@example.com')), 'day');
    $service->confirm($empty, 'stripe');
    ob_end_clean();
    $tests->same(true, false, 'empty booking should throw RuntimeException');
} catch (RuntimeException $e) {
    ob_end_clean();
    $tests->same('Empty booking', $e->getMessage(), 'empty booking throws expected RuntimeException');
}

try {
    ob_start();
    $invalidCustomer = new Customer(5, new Email('not-an-email'));
    $invalidEmail = new Booking(5, $invalidCustomer, 'day');
    $service->confirm($invalidEmail, 'stripe');
    ob_end_clean();
    $tests->same(true, false, 'invalid email should throw RuntimeException');
} catch (RuntimeException $e) {
    ob_end_clean();
    $tests->same('Invalid email', $e->getMessage(), 'invalid email throws expected RuntimeException');
}

try {
    ob_start();
    $invalidQty = createBooking('standard', 'day', 50.0, 0);
    $service->confirm($invalidQty, 'stripe');
    ob_end_clean();
    $tests->same(true, false, 'zero or negative quantity should throw RuntimeException');
} catch (RuntimeException $e) {
    ob_end_clean();
    $tests->same('Invalid quantity', $e->getMessage(), 'zero or negative quantity throws expected RuntimeException');
}

try {
    ob_start();
    $unknownPayment = createBooking('standard', 'day', 50.0, 1);
    $service->confirm($unknownPayment, 'bitcoin');
    ob_end_clean();
    $tests->same(true, false, 'unknown payment method should throw RuntimeException');
} catch (RuntimeException $e) {
    ob_end_clean();
    $tests->same('Unknown payment method', $e->getMessage(), 'unknown payment method throws expected RuntimeException');
}

try {
    ob_start();
    $payfast = createBooking('standard', 'day', 50.0, 1);
    $service->confirm($payfast, 'payfast');
    ob_end_clean();
    $tests->same(true, false, 'payfast currently throws RuntimeException');
} catch (RuntimeException $e) {
    ob_end_clean();
    $tests->same('PayFast not implemented', $e->getMessage(), 'payfast currently throws not implemented RuntimeException');
}

// Tests Ticket #104 : Actions apres confirmation
ob_start();
$withPhoneBooking = createBooking('standard', 'day', 50.0, 1, '0612345678');
$service->confirm($withPhoneBooking, 'stripe');
$outputWithPhone = ob_get_clean();

$tests->same(true, str_contains($outputWithPhone, 'EMAIL test@example.com'), 'ticket #104: confirmation email is sent');
$tests->same(true, str_contains($outputWithPhone, 'LOYALTY customer=1 points=50'), 'ticket #104: loyalty points are added');
$tests->same(true, str_contains($outputWithPhone, 'ANALYTICS booking_confirmed'), 'ticket #104: analytics event is tracked');
$tests->same(true, str_contains($outputWithPhone, 'SMS 0612345678'), 'ticket #104: SMS is sent when phone is provided');

ob_start();
$withoutPhoneBooking = createBooking('standard', 'day', 50.0, 1, null);
$service->confirm($withoutPhoneBooking, 'stripe');
$outputWithoutPhone = ob_get_clean();

$tests->same(false, str_contains($outputWithoutPhone, 'SMS'), 'ticket #104: SMS is not sent when phone is null');

// Test extensibilite du Pattern Observer (ajout dynamique d'un listener)
$customCalled = false;
$customListener = new class($customCalled) implements BookingConfirmationListener {
    public function __construct(private bool &$called) {}
    public function onBookingConfirmed(Booking $booking, float $total): void {
        $this->called = true;
    }
};
$extensibleService = new BookingService();
$extensibleService->addListener($customListener);
confirmBooking($extensibleService, createBooking());
$tests->same(true, $customCalled, 'ticket #104: new reaction can be added dynamically without modifying BookingService (OCP)');

$tests->summary();
