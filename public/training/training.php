<?php
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_once dirname(__DIR__, 2) . '/app/Views/languages/index.php';

$languages = (new \App\Controllers\LanguageController\LanguageController())->index();
page_start('Technology training', 'Explore programming languages and practical skills taught at TechWorld Academy.');
render_languages_view($languages);
page_end();
