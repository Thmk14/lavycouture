<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
session_destroy();
setcookie("id_personnel", "", time() - 3600, "/"); // Expirer le cookie
header("Location: index.php");
exit();
?>
