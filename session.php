<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require 'config.php';

if (!isset($_SESSION['id_client']) && isset($_COOKIE['id_client'])) {
    $_SESSION['id_client'] = $_COOKIE['id_client'];
}

$isLoggedIn = isset($_SESSION['id_client']);
?>
