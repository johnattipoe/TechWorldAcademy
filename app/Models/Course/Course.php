<?php
declare(strict_types=1);

namespace App\Models\Course;

final class Course
{
    public static function catalogue(): array
    {
        $courses = [
            ['title' => 'Web Development', 'description' => 'Create modern, accessible websites with HTML, CSS, JavaScript and practical projects.', 'level' => 'Beginner', 'duration' => '8 weeks', 'icon' => '{ }', 'color' => 'icon-blue'],
            ['title' => 'Data Analytics', 'description' => 'Work with spreadsheets, SQL and data visualisation to answer useful questions.', 'level' => 'Beginner', 'duration' => '10 weeks', 'icon' => '◈', 'color' => 'icon-orange'],
            ['title' => 'AI & Automation', 'description' => 'Understand generative AI and build thoughtful workflows for real tasks.', 'level' => 'All levels', 'duration' => '6 weeks', 'icon' => '✳', 'color' => 'icon-green'],
            ['title' => 'Cybersecurity Essentials', 'description' => 'Learn core security concepts, safe practices and how to spot common threats.', 'level' => 'Beginner', 'duration' => '6 weeks', 'icon' => '⌁', 'color' => 'icon-purple'],
            ['title' => 'Cloud Computing', 'description' => 'Explore cloud foundations, deployment concepts and essential cloud services.', 'level' => 'Intermediate', 'duration' => '8 weeks', 'icon' => '☁', 'color' => 'icon-blue'],
            ['title' => 'Python Programming', 'description' => 'Build a strong programming foundation and automate useful everyday work.', 'level' => 'Beginner', 'duration' => '8 weeks', 'icon' => '⌘', 'color' => 'icon-orange'],
            ['title' => 'UI/UX Design', 'description' => 'Learn user research, wireframing and interface design principles to plan clear digital experiences.', 'level' => 'Beginner', 'duration' => '6 weeks', 'icon' => 'UX', 'color' => 'icon-purple'],
            ['title' => 'Software Engineering', 'description' => 'Explore software design, version control, testing and collaborative development workflows.', 'level' => 'Intermediate', 'duration' => '12 weeks', 'icon' => 'SE', 'color' => 'icon-blue'],
            ['title' => 'IT Support Essentials', 'description' => 'Build practical foundations in troubleshooting, operating systems, hardware and user support.', 'level' => 'Beginner', 'duration' => '5 weeks', 'icon' => 'IT', 'color' => 'icon-green'],
            ['title' => 'Networking Fundamentals', 'description' => 'Understand network concepts, common protocols, connectivity and foundational network security.', 'level' => 'Beginner', 'duration' => '6 weeks', 'icon' => 'NET', 'color' => 'icon-blue'],
            ['title' => 'Database Design and SQL', 'description' => 'Plan relational data structures and practise querying, joining and maintaining data with SQL.', 'level' => 'Beginner', 'duration' => '8 weeks', 'icon' => 'SQL', 'color' => 'icon-orange'],
            ['title' => 'Mobile App Development', 'description' => 'Explore mobile application structure, interface patterns and the process of building app features.', 'level' => 'Intermediate', 'duration' => '10 weeks', 'icon' => 'APP', 'color' => 'icon-purple'],
            ['title' => 'Digital Marketing', 'description' => 'Learn digital campaign fundamentals, audience research, content planning and performance measurement.', 'level' => 'Beginner', 'duration' => '6 weeks', 'icon' => 'MKT', 'color' => 'icon-orange'],
            ['title' => 'Product Management Foundations', 'description' => 'Explore user needs, product discovery, prioritisation and communication across a product team.', 'level' => 'All levels', 'duration' => '6 weeks', 'icon' => 'PM', 'color' => 'icon-green'],
        ];
        $labels = [
            'web development' => ['accessType' => 'free', 'programType' => 'course', 'trending' => false, 'isNew' => false, 'popular' => true],
            'data analytics' => ['accessType' => 'free_trial', 'programType' => 'certificate', 'trending' => true, 'isNew' => false, 'popular' => true],
            'ai & automation' => ['accessType' => 'free_trial', 'programType' => 'certificate', 'trending' => true, 'isNew' => true, 'popular' => true],
            'cybersecurity essentials' => ['accessType' => 'free', 'programType' => 'course', 'trending' => true, 'isNew' => false, 'popular' => true],
            'cloud computing' => ['accessType' => 'free_trial', 'programType' => 'course', 'trending' => false, 'isNew' => true, 'popular' => false],
            'python programming' => ['accessType' => 'free', 'programType' => 'course', 'trending' => true, 'isNew' => false, 'popular' => true],
            'ui/ux design' => ['accessType' => 'free_trial', 'programType' => 'certificate', 'trending' => false, 'isNew' => true, 'popular' => false],
            'software engineering' => ['accessType' => 'paid', 'programType' => 'degree_pathway', 'trending' => true, 'isNew' => false, 'popular' => true],
            'it support essentials' => ['accessType' => 'free', 'programType' => 'certificate', 'trending' => false, 'isNew' => false, 'popular' => true],
            'networking fundamentals' => ['accessType' => 'free_trial', 'programType' => 'course', 'trending' => false, 'isNew' => false, 'popular' => false],
            'database design and sql' => ['accessType' => 'free', 'programType' => 'course', 'trending' => true, 'isNew' => false, 'popular' => false],
            'mobile app development' => ['accessType' => 'paid', 'programType' => 'degree_pathway', 'trending' => false, 'isNew' => true, 'popular' => false],
            'digital marketing' => ['accessType' => 'free', 'programType' => 'course', 'trending' => false, 'isNew' => true, 'popular' => false],
            'product management foundations' => ['accessType' => 'free_trial', 'programType' => 'certificate', 'trending' => true, 'isNew' => false, 'popular' => false],
        ];
        foreach ($courses as &$course) {
            $course = array_replace($course, $labels[mb_strtolower($course['title'])] ?? []);
            $course['price'] = (float) ($course['price'] ?? 0);
            $course['currency'] = (string) ($course['currency'] ?? 'GHS');
        }
        unset($course);
        return $courses;
    }

    public static function validate(array $input): array
    {
        $data = [
            'title' => trim((string) ($input['title'] ?? '')),
            'description' => trim((string) ($input['description'] ?? '')),
            'level' => trim((string) ($input['level'] ?? '')),
            'duration' => trim((string) ($input['duration'] ?? '')),
            'icon' => trim((string) ($input['icon'] ?? '</>')),
            'color' => trim((string) ($input['color'] ?? 'icon-blue')),
            'published' => isset($input['published']) && (string) $input['published'] === '1',
            'instructorId' => trim((string) ($input['instructor_id'] ?? '')),
            'accessType' => trim((string) ($input['access_type'] ?? 'free')),
            'price' => (float) ($input['price'] ?? 0),
            'currency' => strtoupper(trim((string) ($input['currency'] ?? 'GHS'))),
            'programType' => trim((string) ($input['program_type'] ?? 'course')),
            'trending' => isset($input['trending']) && (string) $input['trending'] === '1',
            'isNew' => isset($input['is_new']) && (string) $input['is_new'] === '1',
            'popular' => isset($input['popular']) && (string) $input['popular'] === '1',
        ];
        $errors = [];
        if ($data['title'] === '' || mb_strlen($data['title']) > 100) $errors['title'] = 'Add a title up to 100 characters.';
        if (mb_strlen($data['description']) < 20 || mb_strlen($data['description']) > 1000) $errors['description'] = 'Write a description between 20 and 1,000 characters.';
        if (!in_array($data['level'], ['Beginner', 'Intermediate', 'Advanced', 'All levels'], true)) $errors['level'] = 'Choose a valid course level.';
        if ($data['duration'] === '' || mb_strlen($data['duration']) > 60) $errors['duration'] = 'Add a duration up to 60 characters.';
        if ($data['icon'] === '' || mb_strlen($data['icon']) > 12) $errors['icon'] = 'Add a short course icon or symbol.';
        if (!in_array($data['color'], ['icon-blue', 'icon-orange', 'icon-green', 'icon-purple'], true)) $errors['color'] = 'Choose a valid course accent.';
        if (!in_array($data['accessType'], ['free', 'free_trial', 'paid'], true)) $errors['access_type'] = 'Choose a valid access label.';
        if (!is_finite($data['price']) || $data['price'] < 0 || $data['price'] > 10000000) $errors['price'] = 'Enter a valid course price.';
        if (round($data['price'], 2) !== $data['price']) $errors['price'] = 'Course prices can have up to two decimal places.';
        if (!preg_match('/^[A-Z]{3}$/', $data['currency'])) $errors['currency'] = 'Enter a three-letter currency code.';
        if ($data['accessType'] === 'paid' && $data['price'] <= 0) $errors['price'] = 'Paid courses need a price greater than zero.';
        if (in_array($data['accessType'], ['paid', 'free_trial'], true) && $data['price'] > 0 && $data['currency'] !== 'GHS') $errors['currency'] = 'Paystack mobile money checkout currently uses GHS.';
        if (!in_array($data['programType'], ['course', 'certificate', 'degree_pathway'], true)) $errors['program_type'] = 'Choose a valid program format.';
        return [$data, $errors];
    }
}
