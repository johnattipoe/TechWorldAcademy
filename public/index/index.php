<?php
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_once dirname(__DIR__, 2) . '/app/Views/home/index.php';

$home = (new \App\Controllers\HomeController\HomeController())->index();
page_start('Build your future in tech', 'Learn practical technology skills with flexible, career-focused courses at TechWorld Academy.');
render_home_view($home);
page_end();
