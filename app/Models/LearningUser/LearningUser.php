<?php
declare(strict_types=1);

namespace App\Models\LearningUser;

use App\Services\FirestoreService\FirestoreConflictException;
use App\Services\FirestoreService\FirestoreService;
use DateTimeImmutable;
use DateTimeZone;
use RuntimeException;

final class LearningUser
{
    public static function setRole(FirestoreService $firestore, string $id, string $role): void
    {
        if (!preg_match('/^[a-f0-9]{64}$/', $id) || !in_array($role, ['student', 'instructor', 'admin'], true)) {
            throw new RuntimeException('Invalid learning account role update.');
        }
        $user = $firestore->get('academy_users', $id);
        if (!$user) throw new RuntimeException('Learning account not found.');
        $firestore->update('academy_users', $id, ['role' => $role]);
    }

    public static function setStatus(FirestoreService $firestore, string $id, string $status): void
    {
        if (!preg_match('/^[a-f0-9]{64}$/', $id) || !in_array($status, ['active', 'disabled'], true)) {
            throw new RuntimeException('Invalid learning account status update.');
        }
        if (!$firestore->get('academy_users', $id)) throw new RuntimeException('Learning account not found.');
        $firestore->update('academy_users', $id, ['status' => $status]);
    }

    public static function idForEmail(string $email): string
    {
        return hash('sha256', mb_strtolower(trim($email)));
    }

    public static function createStudent(FirestoreService $firestore, string $name, string $email, string $password): array
    {
        $id = self::idForEmail($email);
        try {
            $firestore->create('academy_users', $id, [
                'name' => trim($name),
                'email' => mb_strtolower(trim($email)),
                'passwordHash' => password_hash($password, PASSWORD_DEFAULT),
                'role' => 'student',
                'createdAt' => new DateTimeImmutable('now', new DateTimeZone('UTC')),
                'status' => 'active',
            ]);
        } catch (FirestoreConflictException $exception) {
            throw new RuntimeException('An account with that email already exists.', 0, $exception);
        }
        return ['id' => $id, 'name' => trim($name), 'email' => mb_strtolower(trim($email)), 'role' => 'student'];
    }

    public static function findByEmail(FirestoreService $firestore, string $email): ?array
    {
        return $firestore->get('academy_users', self::idForEmail($email));
    }

    public static function promoteBootstrapAdmin(FirestoreService $firestore, string $email, string $password, string $name): array
    {
        $lockId = 'admin-bootstrap';
        if ($firestore->get('academy_security', $lockId)) throw new RuntimeException('Initial admin setup has already been completed.');

        $user = self::findByEmail($firestore, $email);
        if (!$user) {
            $user = self::createStudent($firestore, $name, $email, $password);
        } elseif (!password_verify($password, (string) ($user['passwordHash'] ?? ''))) {
            throw new RuntimeException('The learning account password must match the configured initial admin password.');
        }

        if (($user['role'] ?? '') !== 'admin') self::setRole($firestore, (string) $user['id'], 'admin');
        try {
            $firestore->create('academy_security', $lockId, [
                'purpose' => 'initial_admin_bootstrap_completed',
                'adminUserId' => (string) $user['id'],
                'completedAt' => new DateTimeImmutable('now', new DateTimeZone('UTC')),
            ]);
        } catch (FirestoreConflictException $exception) {
            throw new RuntimeException('Initial admin setup was completed by another request. Sign in again.', 0, $exception);
        }
        $user = self::findByEmail($firestore, $email) ?? throw new RuntimeException('Initial admin account could not be loaded after setup.');
        $user['role'] = 'admin';
        return $user;
    }
}
