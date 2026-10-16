<?php
declare(strict_types=1);

namespace App\Services\FirestoreService;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use InvalidArgumentException;

final class FirestoreValueCodec
{
    public static function encodeFields(array $fields): array
    {
        $result = [];
        foreach ($fields as $name => $value) {
            if (!is_string($name) || $name === '') throw new InvalidArgumentException('Firestore field names must be non-empty strings.');
            $result[$name] = self::encodeValue($value);
        }
        return $result;
    }

    public static function decodeFields(array $fields): array
    {
        $result = [];
        foreach ($fields as $name => $value) $result[$name] = self::decodeValue($value);
        return $result;
    }

    private static function encodeValue($value): array
    {
        if ($value === null) return ['nullValue' => 'NULL_VALUE'];
        if (is_bool($value)) return ['booleanValue' => $value];
        if (is_int($value)) return ['integerValue' => (string) $value];
        if (is_float($value)) return ['doubleValue' => $value];
        if (is_string($value)) return ['stringValue' => $value];
        if ($value instanceof DateTimeInterface) {
            return ['timestampValue' => DateTimeImmutable::createFromInterface($value)->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d\TH:i:s.u\Z')];
        }
        if (!is_array($value)) throw new InvalidArgumentException('Unsupported Firestore field value.');
        if ($value === [] || array_keys($value) === range(0, count($value) - 1)) {
            return ['arrayValue' => ['values' => array_map([self::class, 'encodeNestedValue'], $value)]];
        }
        return ['mapValue' => ['fields' => self::encodeFields($value)]];
    }

    public static function encodeNestedValue($value): array
    {
        return self::encodeValue($value);
    }

    private static function decodeValue(array $value)
    {
        if (array_key_exists('nullValue', $value)) return null;
        if (array_key_exists('stringValue', $value)) return $value['stringValue'];
        if (array_key_exists('booleanValue', $value)) return (bool) $value['booleanValue'];
        if (array_key_exists('integerValue', $value)) {
            $integer = (string) $value['integerValue'];
            return filter_var($integer, FILTER_VALIDATE_INT) !== false ? (int) $integer : $integer;
        }
        if (array_key_exists('doubleValue', $value)) return (float) $value['doubleValue'];
        if (array_key_exists('timestampValue', $value)) return new DateTimeImmutable((string) $value['timestampValue']);
        if (array_key_exists('arrayValue', $value)) {
            $values = $value['arrayValue']['values'] ?? [];
            return is_array($values) ? array_map([self::class, 'decodeNestedValue'], $values) : [];
        }
        if (array_key_exists('mapValue', $value)) {
            $fields = $value['mapValue']['fields'] ?? [];
            return is_array($fields) ? self::decodeFields($fields) : [];
        }
        return null;
    }

    public static function decodeNestedValue(array $value)
    {
        return self::decodeValue($value);
    }
}
