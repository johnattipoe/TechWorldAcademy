<?php
declare(strict_types=1);

namespace App\Controllers\LanguageController;

use App\Models\ProgrammingLanguage\ProgrammingLanguage;

final class LanguageController
{
    public function index(): array
    {
        return ProgrammingLanguage::catalogue();
    }

    public function find(string $slug): ?array
    {
        foreach ($this->index() as $language) {
            if (hash_equals($language['slug'], $slug)) return $language;
        }
        return null;
    }
}
