<?php
session_start();
session_unset(); 
session_destroy();

// Redirect la pagina de login
header("Location: index.php");
exit();
?>