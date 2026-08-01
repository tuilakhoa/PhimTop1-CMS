<?php
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}
include 'includes/header.php';
include 'includes/hero.php';
include 'includes/features.php';
include 'includes/docs.php';
include 'includes/cta.php';
include 'includes/footer.php';
?>
