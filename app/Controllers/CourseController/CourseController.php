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
        if (env_value('FIREBASE_PROJECT_ID') && env_value('FIREBASE_CREDENTIALS')) {
            try {
                $stored = (new FirestoreService(FirebaseService::firestore()))->list('academy_courses', 100, 'title', 'ASCENDING');
                $published = array_values(array_filter($stored, static fn(array $course): bool => (bool) ($course['published'] ?? false)));
                $starterTitles = array_map(static fn(array $course): string => mb_strtolower($course['title']), Course::catalogue());
                foreach ($published as $course) {
                    $title = mb_strtolower((string) ($course['title'] ?? ''));
                    if ($title === '' || in_array($title, $starterTitles, true)) continue;
                    $course['description'] = (string) ($course['description'] ?? '');
                    $course['level'] = (string) ($course['level'] ?? 'All levels');
                    $course['duration'] = (string) ($course['duration'] ?? 'Ask us');
                    $course['icon'] = (string) ($course['icon'] ?? '</>');
                    $course['color'] = (string) ($course['color'] ?? 'icon-blue');
                    $starterTitles[] = $title;
                    $custom[] = $course;
                }
                return array_merge(Course::catalogue(), $custom ?? []);
            } catch (Throwable $error) {
                error_log('Course catalogue could not be loaded from Firestore: ' . $error->getMessage());
            }
        }
        return Course::catalogue();
    }

    public function featured(int $count = 3): array
    {
        return array_slice($this->index(), 0, max(0, $count));
    }
}
