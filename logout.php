<?php
session_start();
$sepet = isset($_SESSION['sepet']) ? $_SESSION['sepet'] : null;
session_destroy();
session_start();
if ($sepet !== null) {
    $_SESSION['sepet'] = $sepet;
}
header('Location: login.php');
exit;
?>
