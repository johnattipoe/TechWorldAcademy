<?php
declare(strict_types=1);

namespace App\Controllers\CourseController;

use App\Models\Course\Course;
use App\Services\FirebaseService\FirebaseService;
use App\Services\FirestoreService\FirestoreService;
use Throwable;

final class CourseController
{
    public function index(): array
    {
        $catalogue = Course::catalogue();
        foreach ($catalogue as &$course) {
            $slug = strtolower(trim((string) preg_replace('/[^A-Za-z0-9]+/', '-', $course['title']), '-'));
            $course['id'] = 'starter-' . $slug;
        }
        unset($course);
        if (env_value('FIREBASE_PROJECT_ID') && env_value('FIREBASE_CREDENTIALS')) {
            try {
                $stored = (new FirestoreService(FirebaseService::firestore()))->list('academy_courses', 100, 'title', 'ASCENDING');
                $storedByTitle = [];
                foreach ($stored as $storedCourse) {
                    $titleKey = mb_strtolower(trim((string) ($storedCourse['title'] ?? '')));
                    if ($titleKey !== '') $storedByTitle[$titleKey] = $storedCourse;
                }
                $catalogue = array_values(array_filter(array_map(static function (array $course) use (&$storedByTitle): ?array {
                    $titleKey = mb_strtolower(trim((string) ($course['title'] ?? '')));
                    if (!isset($storedByTitle[$titleKey])) return $course;
                    $managed = $storedByTitle[$titleKey];
                    unset($storedByTitle[$titleKey]);
                    if (empty($managed['published'])) return null;
                    $stableId = (string) $course['id'];
                    $merged = array_replace($course, $managed);
                    $merged['id'] = $stableId;
                    $merged['managedId'] = (string) ($managed['id'] ?? '');
                    $merged['description'] = (string) ($managed['description'] ?? $course['description']);
                    $merged['level'] = (string) ($managed['level'] ?? $course['level']);
                    $merged['duration'] = (string) ($managed['duration'] ?? $course['duration']);
                    $merged['icon'] = (string) ($managed['icon'] ?? $course['icon']);
                    $merged['color'] = (string) ($managed['color'] ?? $course['color']);
                    foreach (['accessType', 'price', 'currency', 'programType', 'trending', 'isNew', 'popular'] as $labelField) {
                        $merged[$labelField] = $managed[$labelField] ?? $course[$labelField];
                    }
                    return $merged;
                }, $catalogue)));
                foreach ($storedByTitle as $course) {
                    if (empty($course['published'])) continue;
                    $course['description'] = (string) ($course['description'] ?? '');
                    $course['level'] = (string) ($course['level'] ?? 'All levels');
                    $course['duration'] = (string) ($course['duration'] ?? 'Ask us');
                    $course['icon'] = (string) ($course['icon'] ?? '</>');
                    $course['color'] = (string) ($course['color'] ?? 'icon-blue');
                    $course['accessType'] = (string) ($course['accessType'] ?? 'free');
                    $course['price'] = (float) ($course['price'] ?? 0);
                    $course['currency'] = (string) ($course['currency'] ?? 'GHS');
                    $course['programType'] = (string) ($course['programType'] ?? 'course');
                    $course['trending'] = (bool) ($course['trending'] ?? false);
                    $course['isNew'] = (bool) ($course['isNew'] ?? false);
                    $course['popular'] = (bool) ($course['popular'] ?? false);
                    $course['managedId'] = (string) ($course['id'] ?? '');
                    $custom[] = $course;
                }
                return array_merge($catalogue, $custom ?? []);
            } catch (Throwable $error) {
                error_log('Course catalogue could not be loaded from Firestore: ' . $error->getMessage());
            }
        }
        return $catalogue;
    }

    public function featured(int $count = 3): array
    {
        return array_slice($this->index(), 0, max(0, $count));
    }
}
