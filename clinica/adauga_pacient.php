<?php
include('config.php');

if (!isset($_SESSION['medic_id'])) {
    header("Location: index.php");
    exit();
}

$mesaj = "";
$succes = false;
$noul_id = 0;

if (isset($_POST['salveaza_pacient'])) {
    $nume = $_POST['nume'];
    $prenume = $_POST['prenume'];
    $cnp = preg_replace('/[^0-9]/', '', $_POST['cnp']); // orice nu e cifra
    $data_nasterii = $_POST['data_nasterii'];
    $sex = $_POST['sex'];
    $telefon = preg_replace('/[^0-9]/', '', $_POST['telefon']); 
    $email = $_POST['email'];
    $adresa = $_POST['adresa'];

    // Validare
    if (strlen($cnp) !== 13) {
        $mesaj = "<div class='alert-error'>❌ Eroare: CNP-ul trebuie să conțină exact 13 cifre!</div>";
    } elseif (strlen($telefon) !== 10) {
        $mesaj = "<div class='alert-error'>❌ Eroare: Numărul de telefon trebuie să conțină exact 10 cifre!</div>";
    } else {
        $sql = "INSERT INTO pacienti (nume, prenume, cnp, data_nasterii, sex, telefon, email, adresa) 
                VALUES ('$nume', '$prenume', '$cnp', '$data_nasterii', '$sex', '$telefon', '$email', '$adresa')";

        if (mysqli_query($conn, $sql)) {
            $noul_id = mysqli_insert_id($conn);
            $mesaj = "<div class='alert-success'>✅ Pacientul a fost înregistrat cu succes!</div>";
            $succes = true;
        } else {
            $mesaj = "<div class='alert-error'>❌ Eroare la salvare: " . mysqli_error($conn) . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Adaugă Pacient - Clinic Pro</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .form-container { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); max-width: 800px; }
        .row { display: flex; gap: 20px; margin-bottom: 15px; }
        .col { flex: 1; }
        label { display: block; margin-bottom: 5px; font-weight: 600; color: #34495e; }
        input, select, textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; }
        input:focus { border-color: #0984e3; outline: none; box-shadow: 0 0 5px rgba(9,132,227,0.2); }
        .alert-success { background: #dff9fb; color: #0984e3; padding: 15px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #74b9ff; }
        .alert-error { background: #ff7675; color: white; padding: 15px; border-radius: 6px; margin-bottom: 20px; }
        .btn-submit { background: #0984e3; color: white; border: none; padding: 12px 25px; border-radius: 6px; cursor: pointer; font-size: 16px; font-weight: bold; transition: 0.3s; }
        .btn-submit:hover { background: #0873c4; }
        .info-label { font-size: 11px; color: #7f8c8d; font-weight: normal; }
        .error-hint { color: #e74c3c; font-size: 11px; display: none; margin-top: 4px; }

        .btn-action { display: inline-block; padding: 10px 20px; margin-top: 10px; border-radius: 6px; text-decoration: none; font-weight: bold; color: white; margin-right: 10px; }
        .btn-prog { background: #6c5ce7; }
        .btn-dosar { background: #27ae60; }
    </style>
</head>
<body>

<?php include('sidebar.php'); ?>

<div class="main-content">
    <h1>👤 Adăugare Pacient Nou</h1>
    <p>Introduceți CNP-ul pentru completare automată și asigurați-vă că datele sunt corecte.</p>
    
    <?php echo $mesaj; ?>

    <?php if($succes): ?>
        <div style="background: #f1f2f6; padding: 20px; border-radius: 10px; margin-bottom: 20px; border: 1px solid #dfe6e9;">
            <h4 style="margin-top:0;">Acțiuni rapide pentru noul pacient:</h4>
            <a href="programari.php?pacient_id=<?php echo $noul_id; ?>" class="btn-action btn-prog">📅 Programează Vizită</a>
            <a href="profil_pacient.php?id=<?php echo $noul_id; ?>" class="btn-action btn-dosar">👁️ Vezi Dosar</a>
        </div>
    <?php endif; ?>

    <div class="form-container">
        <form method="POST" id="patientForm">
            <h3 style="margin-top:0; color: #0984e3;">1. Identitate</h3>
            
            <div class="row">
                <div class="col">
                    <label>CNP <span class="info-label">(trebuie să aibă 13 cifre)</span></label>
                    <input type="text" name="cnp" id="cnp_input" maxlength="13" placeholder="621XXXXXXXXXX" required oninput="valideazaCNP()">
                    <span id="cnp_error" class="error-hint">CNP-ul trebuie să aibă exact 13 cifre!</span>
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <label>Nume</label>
                    <input type="text" name="nume" placeholder="ex: Ionescu" required>
                </div>
                <div class="col">
                    <label>Prenume</label>
                    <input type="text" name="prenume" placeholder="ex: Andrei" required>
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <label>Data Nașterii</label>
                    <input type="date" name="data_nasterii" id="data_nasterii" required>
                </div>
                <div class="col">
                    <label>Sex</label>
                    <select name="sex" id="sex_select">
                        <option value="M">Masculin</option>
                        <option value="F">Feminin</option>
                    </select>
                </div>
            </div>

            <hr style="margin: 25px 0; border: 0; border-top: 1px solid #eee;">
            
            <h3 style="color: #0984e3;">2. Date de Contact</h3>
            <div class="row">
                <div class="col">
                    <label>Telefon (10 cifre)</label>
                    <input type="text" name="telefon" id="telefon_input" maxlength="10" placeholder="07XXXXXXXX" required oninput="valideazaTelefon()">
                    <span id="tel_error" class="error-hint">Introduceți exact 10 cifre!</span>
                </div>
                <div class="col">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="pacient@exemplu.com">
                </div>
            </div>

            <div class="row">
                <div class="col">
                    <label>Adresă Domiciliu</label>
                    <textarea name="adresa" rows="3" placeholder="Strada, Număr, Oraș..."></textarea>
                </div>
            </div>

            <button type="submit" name="salveaza_pacient" class="btn-submit">Înregistrează Pacient</button>
        </form>
    </div>
</div>

<script>
function valideazaCNP() {
    let cnpInput = document.getElementById('cnp_input');
    let cnpError = document.getElementById('cnp_error');
    
    // Eliminam orice nu este cifra
    cnpInput.value = cnpInput.value.replace(/[^0-9]/g, '');

    if (cnpInput.value.length > 0 && cnpInput.value.length !== 13) {
        cnpError.style.display = 'block';
        cnpInput.style.borderColor = '#e74c3c';
    } else {
        cnpError.style.display = 'none';
        cnpInput.style.borderColor = '#ddd';
    }

    // Completare automata date
    if (cnpInput.value.length === 13) {
        let cnp = cnpInput.value;
        let s = parseInt(cnp[0]);
        let aa = cnp.substring(1, 3);
        let ll = cnp.substring(3, 5);
        let zz = cnp.substring(5, 7);
        let secol = "";
        
        if (s == 1 || s == 2) secol = "19";
        else if (s == 5 || s == 6) secol = "20";
        else if (s == 3 || s == 4) secol = "18";
        
        if (secol !== "") {
            let anComplet = secol + aa;
            document.getElementById('data_nasterii').value = anComplet + "-" + ll + "-" + zz;
            document.getElementById('sex_select').value = (s % 2 !== 0) ? "M" : "F";
        }
    }
}

function valideazaTelefon() {
    let telInput = document.getElementById('telefon_input');
    let telError = document.getElementById('tel_error');
    
    telInput.value = telInput.value.replace(/[^0-9]/g, '');
    
    if (telInput.value.length > 0 && telInput.value.length !== 10) {
        telError.style.display = 'block';
        telInput.style.borderColor = '#e74c3c';
    } else {
        telError.style.display = 'none';
        telInput.style.borderColor = '#ddd';
    }
}
</script>

</body>
</html>