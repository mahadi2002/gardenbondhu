<?php
declare(strict_types=1);

namespace App\Services;

use RuntimeException;

/** Resolves CARRIER_DRIVER to a concrete gateway. The only place that switches. */
final class GatewayFactory
{
    private static ?SubscriptionGateway $instance = null;

    public static function make(): SubscriptionGateway
    {
        if (self::$instance instanceof SubscriptionGateway) {
            return self::$instance;
        }

        $driver = (string) config('carrier.driver', 'mock');

        if ($driver === 'mock' && config('app.env') === 'production') {
            throw new RuntimeException('MockGateway is blocked in production.');
        }

        return self::$instance = match ($driver) {
            'mock'    => new MockGateway(),
            'carrier' => new CarrierGateway(),
            default   => throw new RuntimeException('Unknown CARRIER_DRIVER: ' . $driver),
        };
    }

    /** Test seam. */
    public static function set(?SubscriptionGateway $gateway): void
    {
        self::$instance = $gateway;
    }
}
