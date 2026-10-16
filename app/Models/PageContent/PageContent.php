<?php
declare(strict_types=1);

namespace App\Models\PageContent;

use App\Services\FirestoreService\FirestoreService;

final class PageContent
{
    public static function pages(): array
    {
        return [
            'about' => ['label' => 'About', 'eyebrow' => 'About TechWorld Academy', 'title' => 'Technology belongs', 'accent' => 'to everyone.', 'description' => 'We make learning technology clear, practical and welcoming, so more people can shape the future of work.'],
            'courses' => ['label' => 'Courses', 'eyebrow' => 'Find your next skill', 'title' => 'Learn something', 'accent' => 'that takes you further.', 'description' => 'Practical courses built to help you grow your confidence and create new opportunities in technology.'],
            'training' => ['label' => 'Training', 'eyebrow' => 'Learn by building', 'title' => 'Get hands-on', 'accent' => 'with technology.', 'description' => 'Build practical foundations in programming, data and modern digital tools with guided projects and supportive instruction.'],
            'services' => ['label' => 'Services', 'eyebrow' => 'For teams and organisations', 'title' => 'Build skills', 'accent' => 'together.', 'description' => 'Bring practical technology learning to your team. We’ll work with you to understand your goals and discuss a suitable training plan.'],
            'contact' => ['label' => 'Contact', 'eyebrow' => 'We’re here to help', 'title' => 'Let’s start', 'accent' => 'a conversation.', 'description' => 'Ask about a course, share what you’re hoping to learn, or tell us what your team needs. We’ll help you find a useful next step.'],
            'faq' => ['label' => 'FAQ', 'eyebrow' => 'Questions, answered', 'title' => 'A few things', 'accent' => 'you might be wondering.', 'description' => 'Still curious? Our team is happy to help you find the details you need.'],
            'resources' => ['label' => 'Resources', 'eyebrow' => 'TechWorld learning resources', 'title' => 'Useful tools for', 'accent' => 'your next step.', 'description' => 'Practical guides and tools to support your technology learning.'],
            'guides' => ['label' => 'Learning guides', 'eyebrow' => 'TechWorld learning resources', 'title' => 'Learning guides', 'accent' => '', 'description' => 'A practical framework for learning technical skills through small, repeatable steps.'],
            'glossary' => ['label' => 'Tech glossary', 'eyebrow' => 'TechWorld learning resources', 'title' => 'Tech glossary', 'accent' => '', 'description' => 'Plain-language definitions for common programming and digital technology terms.'],
            'projects' => ['label' => 'Practice projects', 'eyebrow' => 'TechWorld learning resources', 'title' => 'Practice projects', 'accent' => '', 'description' => 'Starter project briefs to help you turn technology concepts into practical work.'],
            'business' => ['label' => 'For businesses', 'eyebrow' => 'TECHWORLD LEARNING FOR BUSINESS', 'title' => 'Build the skills your team needs for', 'accent' => 'what comes next.', 'description' => 'Give your people a practical way to grow technology skills, explore new tools and put their learning to work.'],
            'universities' => ['label' => 'For universities', 'eyebrow' => 'TECHWORLD LEARNING FOR UNIVERSITIES', 'title' => 'Connect what students learn with', 'accent' => 'what they can build.', 'description' => 'Bring practical technology courses into the student learning experience and give learners a guided way to explore skills beyond the classroom.'],
            'government' => ['label' => 'For government', 'eyebrow' => 'TECHWORLD LEARNING FOR GOVERNMENT', 'title' => 'Practical digital skills for', 'accent' => 'public service.', 'description' => 'Support public sector teams and workforce programs with guided technology learning that helps people build skills step by step.'],
        ];
    }

    public static function get(FirestoreService $firestore, string $key): array
    {
        $defaults = self::pages()[$key] ?? self::pages()['about'];
        return array_replace($defaults, $firestore->get('academy_page_content', $key) ?? []);
    }

    public static function save(FirestoreService $firestore, string $key, array $input): array
    {
        if (!isset(self::pages()[$key])) return ['page' => 'Choose a page from the list.'];
        $data = [];
        foreach (['eyebrow', 'title', 'accent', 'description'] as $field) $data[$field] = trim((string) ($input[$field] ?? ''));
        $errors = [];
        if ($data['eyebrow'] === '' || mb_strlen($data['eyebrow']) > 100) $errors['eyebrow'] = 'The short heading must contain 1 to 100 characters.';
        if ($data['title'] === '' || mb_strlen($data['title']) > 120) $errors['title'] = 'The main heading must contain 1 to 120 characters.';
        if (mb_strlen($data['accent']) > 120) $errors['accent'] = 'The highlighted heading must be 120 characters or fewer.';
        if ($data['description'] === '' || mb_strlen($data['description']) > 600) $errors['description'] = 'The introduction must contain 1 to 600 characters.';
        if ($errors) return $errors;
        if ($firestore->get('academy_page_content', $key)) $firestore->update('academy_page_content', $key, $data);
        else $firestore->create('academy_page_content', $key, $data);
        return [];
    }
}
