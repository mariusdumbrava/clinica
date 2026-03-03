<?php
include('config.php');

if (!isset($_SESSION['medic_id'])) {
    header("Location: index.php");
    exit();
}

$pacient_id = $_GET['id'];

$query_p = "SELECT nume, prenume, alergii FROM pacienti WHERE id = $pacient_id";
$res_p = mysqli_query($conn, $query_p);
$pacient = mysqli_fetch_assoc($res_p);

$mesaj = "";
$succes = false;

if (isset($_POST['salveaza_consult'])) {
    $diagnostic = $_POST['diagnostic'];
    $simptome = $_POST['simptome'];
    $durere_intensitate = $_POST['durere_intensitate'];
    $durere_localizare = $_POST['durere_localizare'];
    $istoric_patologie = $_POST['istoric_patologie'];
    $tratament = $_POST['tratament'];
    $observatii = $_POST['observatii'];
    
    $medic_id = $_SESSION['medic_id'];
    $data_consult = date('Y-m-d');

    $sql = "INSERT INTO consultatii (pacient_id, medic_id, data_consult, diagnostic, simptome, durere_intensitate, durere_localizare, istoric_patologie, tratament, observatii) 
            VALUES ('$pacient_id', '$medic_id', '$data_consult', '$diagnostic', '$simptome', '$durere_intensitate', '$durere_localizare', '$istoric_patologie', '$tratament', '$observatii')";

    if (mysqli_query($conn, $sql)) {
        $mesaj = "<div class='alert-success'>✅ Consultația a fost înregistrată cu succes!</div>";
        $succes = true;
    } else {
        $mesaj = "<div class='alert-error'>❌ Eroare la salvare: " . mysqli_error($conn) . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Consult Nou - <?php echo htmlspecialchars($pacient['nume']); ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        .consult-container { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .alergii-banner { background: #ff7675; color: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: bold; animation: blink 2s infinite; }
        @keyframes blink { 0% { opacity: 1; } 50% { opacity: 0.7; } 100% { opacity: 1; } }
        
        .pain-scale { display: flex; align-items: center; gap: 20px; background: #f9f9f9; padding: 15px; border-radius: 8px; border: 1px solid #ddd; }
        input[type=range] { flex-grow: 1; cursor: pointer; }
        .pain-value { font-size: 24px; font-weight: bold; color: #d63031; min-width: 30px; }
        
        textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; margin-top: 5px; resize: vertical; font-family: inherit; }
        label { font-weight: bold; display: block; margin-top: 15px; color: #2d3436; }
        
        .btn-consult { background: #00b894; color: white; border: none; padding: 15px 30px; border-radius: 6px; cursor: pointer; font-size: 16px; font-weight: bold; margin-top: 25px; width: 100%; transition: 0.3s; text-align: center; text-decoration: none; display: block; }
        .btn-consult:hover { background: #009477; }
        
        .btn-back-profile { background: #0984e3; color: white; padding: 20px; border-radius: 10px; text-decoration: none; font-weight: bold; display: inline-block; margin-top: 20px; font-size: 18px; }
        .btn-back-profile:hover { background: #0873c4; box-shadow: 0 5px 15px rgba(9,132,227,0.3); }
        
        .success-box { text-align: center; padding: 40px; background: #f0fff4; border: 2px dashed #00b894; border-radius: 15px; margin-top: 20px; }
        .sectiune-noua { margin-top: 20px; padding-top: 15px; border-top: 2px solid #f0f2f5; }
    </style>
</head>
<body>

<?php include('sidebar.php'); ?>

<div class="main-content">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h1>🩺 Consult Nou: <?php echo htmlspecialchars($pacient['nume'] . " " . $pacient['prenume']); ?></h1>
        <a href="profil_pacient.php?id=<?php echo $pacient_id; ?>" style="text-decoration: none; color: #0984e3; font-weight: bold;">⬅️ Înapoi la Profil</a>
    </div>

    <?php echo $mesaj; ?>

    <?php if ($succes): ?>
        <div class="success-box">
            <h2 style="color: #00b894;">🎉 Salvare reușită!</h2>
            <p>Consultația a fost adăugată în istoricul medical al pacientului.</p>
            
            <div style="margin-top: 30px;">
                <a href="profil_pacient.php?id=<?php echo $pacient_id; ?>" class="btn-back-profile">
                    📂 Mergi la Fișa Pacientului
                </a>
                <br><br>
                <a href="lista_consultatii.php" style="color: #636e72; text-decoration: none; font-weight: 600;">
                    📋 Vezi tot registrul de consultații
                </a>
            </div>
        </div>
    <?php else: ?>
        <?php if (!empty($pacient['alergii'])): ?>
            <div class="alergii-banner">
                ⚠️ ATENȚIE ALERGII: <?php echo htmlspecialchars($pacient['alergii']); ?>
            </div>
        <?php endif; ?>

        <div class="consult-container">
            <form method="POST">
                <label>Diagnostic Principal / Trimitere</label>
                <input type="text" name="diagnostic" placeholder="ex: Hipertensiune Arterială gr. II" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px;">

                <label>Simptome Subiective (descrierea pacientului)</label>
                <textarea name="simptome" rows="3" placeholder="Ce acuză pacientul..."></textarea>

                <label>Evaluarea Durerii (Scală 1-10)</label>
                <div class="pain-scale">
                    <span>Fără durere (1)</span>
                    <input type="range" name="durere_intensitate" min="1" max="10" value="1" oninput="this.nextElementSibling.value = this.value">
                    <output class="pain-value">1</output>
                    <span>Insuportabilă (10)</span>
                </div>

                <label>Localizare Durere</label>
                <input type="text" name="durere_localizare" placeholder="ex: Abdominal, cadran superior" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px;">

                <label>Istoric Patologie (Evoluția problemei actuale)</label>
                <textarea name="istoric_patologie" rows="3" placeholder="Debutul simptomelor, tratamente anterioare..."></textarea>

                <div class="sectiune-noua">
                    <label style="color: #0984e3;">💊 Tratament Recomandat (Rețetă/Indicații)</label>
                    <textarea name="tratament" rows="4" placeholder="Ex: 1. Medicament X - 1 tb dimineața..."></textarea>

                    <label style="color: #0984e3;">🔍 Observații și Recomandări suplimentare</label>
                    <textarea name="observatii" rows="3" placeholder="Ex: Revizită peste 30 de zile, regim hiposodat..."></textarea>
                </div>

                <button type="submit" name="salveaza_consult" class="btn-consult">💾 Finalizează și Salvează Consultația</button>
            </form>
        </div>
    <?php endif; ?>
</div>

</body>
</html>