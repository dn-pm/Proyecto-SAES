<?php
require_once __DIR__ . "/config/auth.php";

if (isset($_SESSION['id_user'])) {
    header("Location: " . $base . "/principal.php");
} else {
    header("Location: " . $base . "/login.php");
}
exit;
