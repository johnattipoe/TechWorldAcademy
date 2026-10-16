<?php
declare(strict_types=1);

namespace App\Models\SiteSettings;

use App\Services\FirestoreService\FirestoreService;
use InvalidArgumentException;

final class SiteSettings
{
    public static function defaults(): array
    {
        return [
            'siteName' => (string) \env_value('APP_NAME', 'TechWorld Academy'),
            'contactEmail' => (string) \env_value('CONTACT_EMAIL', 'hello@techworld.academy'),
            'contactPhone' => '',
            'announcementText' => '',
            'announcementEnabled' => false,
            'registrationEnabled' => true,
            'homeHeroTitle' => 'Make your next move',
            'homeHeroAccent' => 'in tech.',
            'homeHeroDescription' => 'Build practical, in-demand skills with expert-led training designed to take you from curious to confident.',
            'learningHeroTitle' => 'Learn skills.',
            'learningHeroAccent' => 'Build what’s next.',
            'learningHeroDescription' => 'Explore practical technology courses, learn at your pace and turn new knowledge into projects you can share.',
        ];
    }

    public static function get(FirestoreService $firestore): array
    {
        return array_replace(self::defaults(), $firestore->get('academy_settings', 'global') ?? []);
    }

    public static function validate(array $input): array
    {
        $data = [
            'siteName' => trim((string) ($input['site_name'] ?? '')),
            'contactEmail' => mb_strtolower(trim((string) ($input['contact_email'] ?? ''))),
            'contactPhone' => trim((string) ($input['contact_phone'] ?? '')),
            'announcementText' => trim((string) ($input['announcement_text'] ?? '')),
            'announcementEnabled' => isset($input['announcement_enabled']) && (string) $input['announcement_enabled'] === '1',
            'registrationEnabled' => isset($input['registration_enabled']) && (string) $input['registration_enabled'] === '1',
            'homeHeroTitle' => trim((string) ($input['home_hero_title'] ?? '')),
            'homeHeroAccent' => trim((string) ($input['home_hero_accent'] ?? '')),
            'homeHeroDescription' => trim((string) ($input['home_hero_description'] ?? '')),
            'learningHeroTitle' => trim((string) ($input['learning_hero_title'] ?? '')),
            'learningHeroAccent' => trim((string) ($input['learning_hero_accent'] ?? '')),
            'learningHeroDescription' => trim((string) ($input['learning_hero_description'] ?? '')),
        ];
        $errors = [];
        if ($data['siteName'] === '' || mb_strlen($data['siteName']) > 80) $errors['site_name'] = 'Enter a site name up to 80 characters.';
        if (!filter_var($data['contactEmail'], FILTER_VALIDATE_EMAIL) || strlen($data['contactEmail']) > 254) $errors['contact_email'] = 'Enter a valid contact email address.';
        if (mb_strlen($data['contactPhone']) > 40) $errors['contact_phone'] = 'Phone number must be 40 characters or fewer.';
        if (mb_strlen($data['announcementText']) > 300) $errors['announcement_text'] = 'Announcement must be 300 characters or fewer.';
        if ($data['announcementEnabled'] && $data['announcementText'] === '') $errors['announcement_text'] = 'Enter announcement text before enabling the banner.';
        foreach (['homeHeroTitle', 'homeHeroAccent', 'learningHeroTitle', 'learningHeroAccent'] as $field) if ($data[$field] === '' || mb_strlen($data[$field]) > 100) $errors[$field] = 'Hero headings must contain 1 to 100 characters.';
        foreach (['homeHeroDescription', 'learningHeroDescription'] as $field) if ($data[$field] === '' || mb_strlen($data[$field]) > 500) $errors[$field] = 'Hero descriptions must contain 1 to 500 characters.';
        return [$data, $errors];
    }

    public static function save(FirestoreService $firestore, array $data): void
    {
        if (!filter_var($data['contactEmail'] ?? null, FILTER_VALIDATE_EMAIL)) throw new InvalidArgumentException('A valid contact email is required.');
        $existing = $firestore->get('academy_settings', 'global');
        if ($existing) $firestore->update('academy_settings', 'global', $data);
        else $firestore->create('academy_settings', 'global', $data);
    }
}
