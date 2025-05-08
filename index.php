<?php
session_start();
if (isset($_SESSION['usuarioID'])) {
    header("Location: templets\Principal.php.");
} else {
    header("Location: templets\login.php.");
}
exit();
?>