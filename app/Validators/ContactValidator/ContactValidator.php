<?php
declare(strict_types=1);

function validate_contact(array $input): array
{
    $data = ['name' => trim((string)($input['name'] ?? '')), 'email' => trim((string)($input['email'] ?? '')), 'message' => trim((string)($input['message'] ?? ''))];
    $errors = [];
    if ($data['name'] === '' || mb_strlen($data['name']) > 100) $errors['name'] = 'Enter your name (up to 100 characters).';
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($data['email']) > 254) $errors['email'] = 'Enter a valid email address.';
    if ($data['message'] === '' || mb_strlen($data['message']) < 10 || mb_strlen($data['message']) > 5000) $errors['message'] = 'Write a message between 10 and 5,000 characters.';
    if (preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $data['message'])) $errors['message'] = 'Your message contains unsupported characters.';
    return [$data, $errors];
}
