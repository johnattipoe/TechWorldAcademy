<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/Validators/ContactValidator/ContactValidator.php';
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

return [
    'valid contact data passes server validation' => static function (): void {
        [, $errors] = validate_contact(['name' => 'Ada Lovelace', 'email' => 'ada@example.com', 'message' => 'I would like course details.']);
        if ($errors !== []) throw new RuntimeException('Expected valid contact data to pass.');
    },
    'invalid contact data is rejected with field errors' => static function (): void {
        [, $errors] = validate_contact(['name' => '', 'email' => 'bad-email', 'message' => 'short']);
        foreach (['name', 'email', 'message'] as $field) {
            if (!isset($errors[$field])) throw new RuntimeException('Missing validation error for ' . $field . '.');
        }
    },
];
