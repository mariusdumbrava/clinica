<?php
include('config.php');

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $parola = $_POST['parola'];

    $query = "SELECT * FROM medici WHERE email='$email' AND parola='$parola'";
    $rezultat = mysqli_query($conn, $query);

    if (mysqli_num_rows($rezultat) == 1) {
        $medic = mysqli_fetch_assoc($rezultat);
        $_SESSION['medic_id'] = $medic['id'];
        $_SESSION['nume_medic'] = $medic['nume'];
        header("Location: dashboard.php");
    } else {
        $eroare = "Date incorecte!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login Clinica</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-body">
    <div class="login-card">
        <h2>Autentificare Medic</h2>
        <form method="POST">
            <input type="email" name="email" placeholder="Email" required><br>
            <input type="password" name="parola" placeholder="Parolă" required><br>
            <button type="submit" name="login">Intră în cont</button>
        </form>
        <?php if(isset($eroare)) echo "<p style='color:red'>$eroare</p>"; ?>
    </div>
</body>
</html>