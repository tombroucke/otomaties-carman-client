<?php

namespace Otomaties\CarmanClient\Booking;

use Illuminate\Support\Arr;

/**
 * Remembers the vehicle, locations and rental period a visitor entered, so other forms can be prefilled
 */
class BookingCookie
{
    public const NAME = 'carman_booking';

    public const FIELDS = [
        'vehicle',
        'pickupLocation',
        'differentReturnLocation',
        'returnLocation',
        'pickupDate',
        'pickupHour',
        'pickupMinute',
        'returnDate',
        'returnHour',
        'returnMinute',
    ];

    public static function get(): array
    {
        $booking = json_decode(base64_decode($_COOKIE[self::NAME] ?? '', true) ?: '', true);

        return is_array($booking) ? self::sanitize($booking) : [];
    }

    /**
     * Merge the given fields into the stored booking
     */
    public static function put(array $booking): void
    {
        $value = base64_encode(json_encode(self::sanitize([...self::get(), ...$booking])));
        $_COOKIE[self::NAME] = $value;

        if (headers_sent()) {
            return;
        }

        setcookie(self::NAME, $value, [
            'expires' => time() + WEEK_IN_SECONDS,
            'path' => COOKIEPATH ?: '/',
            'domain' => COOKIE_DOMAIN ?: '',
            'secure' => is_ssl(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }

    /**
     * Only keep known fields with scalar values, as the input comes from the visitor
     */
    public static function sanitize(array $booking): array
    {
        return array_map(
            fn ($value) => is_scalar($value) ? (string) $value : '',
            Arr::only($booking, self::FIELDS),
        );
    }
}
