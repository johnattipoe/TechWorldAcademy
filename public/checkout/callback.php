<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';

use App\Controllers\CheckoutController\CheckoutController;

$reference = is_string($_GET['reference'] ?? null) ? trim($_GET['reference']) : '';
[$status, $message, $success] = (new CheckoutController())->complete($reference);
http_response_code($status);
page_start($success ? 'Payment confirmed' : 'Payment status', 'Check your Paystack mobile money course payment.');
?>
<section class="section container learning-dashboard-content"><div class="learning-empty"><span class="eyebrow">PAYSTACK MOBILE MONEY</span><h1><?= $success ? 'You’re ready to learn.' : 'We could not confirm payment yet.' ?></h1><p><?= e($message) ?></p><a class="button button-primary" href="/student/my-courses/">Go to my courses <span aria-hidden="true">&rarr;</span></a></div></section>
<?php page_end(); ?>
