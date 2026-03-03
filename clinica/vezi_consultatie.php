<?php
include('config.php');

if (!isset($_SESSION['medic_id'])) {
    header("Location: index.php");
    exit();
}

$id_consult = $_GET['id'];

// Preluam datele consultatiei
$query = "SELECT c.*, p.nume, p.prenume, p.cnp 
          FROM consultatii c 
          JOIN pacienti p ON c.pacient_id = p.id 
          WHERE c.id = $id_consult";

$rezultat = mysqli_query($conn, $query);
$c = mysqli_fetch_assoc($rezultat);

if (!$c) { die("Consultația nu a fost găsită."); }
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Detalii Consultație - <?php echo htmlspecialchars($c['nume']); ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        .fisa-consult {
            background: white;
            padding: 40px;
            max-width: 800px;
            margin: 20px auto;
            border: 1px solid #ddd;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .header-fisa { border-bottom: 2px solid #2d3436; padding-bottom: 10px; margin-bottom: 20px; display: flex; justify-content: space-between; }
        .sectiune { margin-bottom: 25px; }
        .sectiune h4 { color: #0984e3; text-transform: uppercase; border-bottom: 1px solid #eee; padding-bottom: 5px; margin-bottom: 10px; display: flex; align-items: center; gap: 8px; }
        .date-text { white-space: pre-line; background: #f9f9f9; padding: 15px; border-radius: 5px; border-left: 4px solid #0984e3; min-height: 20px; }
        @media print {
            .btn-update, .sidebar, .back-link { display: none !important; }
            .main-content { margin-left: 0 !important; padding: 0 !important; }
            .fisa-consult { border: none; box-shadow: none; margin: 0; width: 100%; max-width: none; }
            body { background: white; }
        }
    </style>
</head>
<body>

<?php include('sidebar.php'); ?>

<div class="main-content">
    <div class="back-link" style="margin-bottom: 20px;">
        <button onclick="window.print()" class="btn-update" style="width: auto;">🖨️ Printează Fișa</button>
        <a href="profil_pacient.php?id=<?php echo $c['pacient_id']; ?>" style="margin-left:15px; text-decoration: none; color: #0984e3; font-weight: bold;">⬅️ Înapoi la profil</a>
    </div>

    <div class="fisa-consult">
        <div class="header-fisa">
            <div>
                <h2 style="margin:0; color: #2d3436;">FIȘĂ CONSULTAȚIE</h2>
                <p>Data vizitei: <strong><?php echo date('d.m.Y', strtotime($c['data_consult'])); ?></strong></p>
            </div>
            <div style="text-align: right;">
                <p style="margin:0;">Pacient: <strong><?php echo htmlspecialchars($c['nume'] . " " . $c['prenume']); ?></strong></p>
                <p style="margin:5px 0 0 0;">CNP: <?php echo htmlspecialchars($c['cnp']); ?></p>
            </div>
        </div>

        <div class="sectiune">
            <h4>🩺 Diagnostic</h4>
            <div class="date-text" style="font-weight: bold; font-size: 1.1em; color: #2d3436;">
                <?php echo htmlspecialchars($c['diagnostic']); ?>
            </div>
        </div>

        <div class="sectiune">
            <h4>📝 Motivele prezentării / Simptome</h4>
            <div class="date-text">
                <?php echo nl2br(htmlspecialchars($c['simptome'])); ?>
                <br><br>
                <strong style="color: #d63031;">Intensitate durere (VAS): <?php echo htmlspecialchars($c['durere_intensitate']); ?>/10</strong>
            </div>
        </div>

        <div class="sectiune">
            <h4>💊 Tratament Recomandat</h4>
            <div class="date-text" style="background: #e3f2fd; border-left-color: #3498db;">
                <?php 
                    // Verificam dacă exista cheia, daca nu punem mesaj default
                    echo isset($c['tratament']) && !empty($c['tratament']) 
                         ? nl2br(htmlspecialchars($c['tratament'])) 
                         : "<em>Nu a fost prescris un tratament specific în timpul acestei vizite.</em>"; 
                ?>
            </div>
        </div>

        <div class="sectiune">
            <h4>🔍 Observații și Recomandări</h4>
            <div class="date-text">
                <?php 
                    echo isset($c['observatii']) && !empty($c['observatii']) 
                         ? nl2br(htmlspecialchars($c['observatii'])) 
                         : "Nicio observație suplimentară."; 
                ?>
            </div>
        </div>

        <div style="margin-top: 60px; display: flex; justify-content: space-between; align-items: flex-end;">
            <div>
                <p style="margin:0;">Data emiterii: <?php echo date('d.m.Y'); ?></p>
            </div>
            <div style="text-align: center;">
                <div style="border-top: 1px solid #2d3436; width: 200px; padding-top: 5px;">
                    Semnătură și Parafă Medic
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>