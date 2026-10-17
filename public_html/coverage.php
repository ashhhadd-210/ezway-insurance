<?php
// The Coverage page became "Auto insurance". Keep old links working.
require __DIR__ . '/app/bootstrap.php';
header('Location: ' . lurl('auto-insurance'), true, 301);
