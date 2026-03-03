<?php
include('config.php');
if (!isset($_SESSION['medic_id'])) { header("Location: index.php"); exit(); }

$selected_pacient_id = isset($_GET['pacient_id']) ? $_GET['pacient_id'] : 0;
$mesaj = "";
$succes_complet = false; // Flag pentru a afișa ecranul de succes

// set implicit data
$data_selectata = isset($_POST['data_p']) ? $_POST['data_p'] : date('Y-m-d');

if (isset($_POST['adauga_programare'])) {
    $p_id = $_POST['pacient_id'];
    $data = $_POST['data_p'];
    $ora = $_POST['ora_p'];
    $motiv = $_POST['motiv'];
    $m_id = $_SESSION['medic_id'];

    $check_sql = "SELECT id FROM programari WHERE data_programare = '$data' AND ora_programare = '$ora' AND status = 'activ'";
    $check_res = mysqli_query($conn, $check_sql);

    if (mysqli_num_rows($check_res) > 0) {
        $mesaj = "<div class='alert-error'>⚠️ Eroare: Acest interval a fost ocupat recent.</div>";
    } else {
        $sql = "INSERT INTO programari (pacient_id, medic_id, data_programare, ora_programare, motiv) 
                VALUES ('$p_id', '$m_id', '$data', '$ora', '$motiv')";
        if (mysqli_query($conn, $sql)) {
            $succes_complet = true;
        }
    }
}

// Functie pentru intervale orare
function genereaza_intervale($conn, $data) {
    $intervale = [];
    $start = strtotime('08:00');
    $end = strtotime('16:00');
    $ocupate = [];
    $res = mysqli_query($conn, "SELECT ora_programare FROM programari WHERE data_programare = '$data' AND status = 'activ'");
    while($row = mysqli_fetch_assoc($res)) { $ocupate[] = substr($row['ora_programare'], 0, 5); }

    $curent = $start;
    while($curent < $end) {
        $ora_format = date('H:i', $curent);
        $este_ocupat = in_array($ora_format, $ocupate);
        $intervale[] = ['ora' => $ora_format, 'ocupat' => $este_ocupat];
        $curent = strtotime('+20 minutes', $curent);
    }
    return $intervale;
}

$pacienti = mysqli_query($conn, "SELECT id, nume, prenume FROM pacienti ORDER BY nume ASC");
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Programare</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .booking-card { max-width: 600px; margin: 30px auto; background: white; padding: 30px; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .form-control { width: 100%; padding: 12px; border: 1px solid #dfe6e9; border-radius: 8px; font-size: 16px; margin-top: 5px; box-sizing: border-box; }
        .grid-time { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-top: 10px; }
        .time-slot { padding: 10px; text-align: center; border: 1px solid #ddd; border-radius: 5px; cursor: pointer; font-size: 14px; transition: 0.2s; }
        .time-slot.available:hover { background: #0984e3; color: white; }
        .time-slot.occupied { background: #f1f2f6; color: #b2bec3; cursor: not-allowed; border: 1px solid #eee; }
        .time-slot.selected { background: #27ae60 !important; color: white !important; border-color: #27ae60; }
        input[type="radio"] { display: none; }
        .btn-save { background: #0984e3; color: white; border: none; width: 100%; padding: 15px; border-radius: 8px; font-size: 18px; font-weight: bold; cursor: pointer; margin-top: 20px; }
        
        /* Stiluri pentru ecranul de succes */
        .success-screen { text-align: center; padding: 40px 20px; }
        .success-icon { font-size: 60px; color: #27ae60; margin-bottom: 20px; }
        .btn-outline { display: inline-block; margin-top: 20px; padding: 12px 25px; border: 2px solid #0984e3; color: #0984e3; text-decoration: none; border-radius: 8px; font-weight: bold; transition: 0.3s; }
        .btn-outline:hover { background: #0984e3; color: white; }
    </style>
</head>
<body>

<?php include('sidebar.php'); ?>

<div class="main-content">
    <div class="booking-card">
        
        <?php if ($succes_complet): ?>
            <div class="success-screen">
                <div class="success-icon">✅</div>
                <h2 style="color: #2d3436;">Programare Reușită!</h2>
                <p style="color: #636e72;">Pacientul a fost adăugat în agenda zilei de <br><strong><?php echo date('d.m.Y', strtotime($data)); ?></strong> la ora <strong><?php echo $ora; ?></strong>.</p>
                
                <a href="dashboard.php" class="btn-save" style="text-decoration: none; display: block;">Mergi la Dashboard</a>
                <a href="lista_pacienti.php" class="btn-outline">Înapoi la Lista Pacienți</a>
            </div>

        <?php else: ?>
            <h2 style="text-align:center; color: #2d3436; margin-bottom: 25px;">📅 Programare Nouă</h2>
            <?php echo $mesaj; ?>

            <form method="POST">
                <div style="margin-bottom: 20px;">
                    <label><strong>1. Pacient</strong></label>
                    <select name="pacient_id" class="form-control" required>
                        <option value="">-- Alege Pacient --</option>
                        <?php mysqli_data_seek($pacienti, 0); while($p = mysqli_fetch_assoc($pacienti)): ?>
                            <option value="<?php echo $p['id']; ?>" <?php echo ($p['id'] == $selected_pacient_id) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($p['nume'] . " " . $p['prenume']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div style="margin-bottom: 20px;">
                    <label><strong>2. Data</strong></label>
                    <input type="date" name="data_p" id="data_p" class="form-control" 
                           value="<?php echo $data_selectata; ?>" 
                           min="<?php echo date('Y-m-d'); ?>" 
                           onchange="schimbaData()">
                </div>

                <label><strong>3. Ora (Intervale de 20 min)</strong></label>
                <div class="grid-time">
                    <?php 
                    $ore = genereaza_intervale($conn, $data_selectata);
                    foreach($ore as $o): 
                        if($o['ocupat']): ?>
                            <div class="time-slot occupied"><?php echo $o['ora']; ?></div>
                        <?php else: ?>
                            <label class="time-slot available">
                                <input type="radio" name="ora_p" value="<?php echo $o['ora']; ?>" required onclick="selectSlot(this)">
                                <?php echo $o['ora']; ?>
                            </label>
                        <?php endif; 
                    endforeach; ?>
                </div>

                <div style="margin-top: 20px;">
                    <label><strong>4. Motiv</strong></label>
                    <textarea name="motiv" class="form-control" rows="2" placeholder="Ex: Control cardiologic..."></textarea>
                </div>

                <button type="submit" name="adauga_programare" class="btn-save">Salvează Programarea</button>
            </form>
        <?php endif; ?>

    </div>
</div>

<script>l
    function schimbaData() {
        const dataInput = document.getElementById('data_p');
        const form = dataInput.closest('form');
        form.submit();
    }

    function selectSlot(input) {
        document.querySelectorAll('.time-slot').forEach(el => el.classList.remove('selected'));
        input.parentElement.classList.add('selected');
    }
</script>

</body>
</html>