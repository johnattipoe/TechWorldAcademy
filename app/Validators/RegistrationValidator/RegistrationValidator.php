<?php
declare(strict_types=1);

namespace App\Validators\RegistrationValidator;

final class RegistrationValidator
{
    public static function validate(array $input): array
    {
        $name = $input['name'] ?? '';
        $email = $input['email'] ?? '';
        $password = $input['password'] ?? '';
        $confirmation = $input['password_confirmation'] ?? '';
        $data = [
            'name' => is_string($name) ? trim($name) : '',
            'email' => is_string($email) ? mb_strtolower(trim($email)) : '',
            'password' => is_string($password) ? $password : '',
            'password_confirmation' => is_string($confirmation) ? $confirmation : '',
        ];
        $errors = [];
        if ($data['name'] === '' || mb_strlen($data['name']) > 100) $errors['name'] = 'Enter your name (up to 100 characters).';
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL) || strlen($data['email']) > 254) $errors['email'] = 'Enter a valid email address.';
        if (strlen($data['password']) < 12 || strlen($data['password']) > 72) $errors['password'] = 'Use a password between 12 and 72 bytes.';
        if (!hash_equals($data['password'], $data['password_confirmation'])) $errors['password_confirmation'] = 'The passwords do not match.';
        return [$data, $errors];
    }
}
