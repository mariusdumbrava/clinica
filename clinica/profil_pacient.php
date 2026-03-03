<?php
include('config.php');

if (!isset($_SESSION['medic_id'])) {
    header("Location: index.php");
    exit();
}

$id = $_GET['id'];
$mesaj = "";

// Salvare actualizari ext
if (isset($_POST['actualizeaza_dosar'])) {
    $nume_tel = $_POST['telefon'];
    $nume_email = $_POST['email'];
    $adr = $_POST['adresa'];
    $grup_post = $_POST['grup_sangvin']; 
    $greutate = $_POST['greutate']; 
    $inaltime = $_POST['inaltime'];
    $ocupatie = $_POST['ocupatie']; 
    $alergii = $_POST['alergii']; 
    $ant_pers = $_POST['ant_personale'];
    $ant_fam = $_POST['ant_familie']; 
    $factori = $_POST['factori_mediu'];

    // Examen Clinic
    $ta = $_POST['tensiune_arteriala'];
    $av = $_POST['alura_ventriculara'];
    $auscultatie = $_POST['auscultatie_cardio'];
    $ekg = $_POST['observatii_ekg'];

    // Laborator
    $glicemie = $_POST['lab_glicemie'];
    $chol = $_POST['lab_cholesterol'];
    $hgb = $_POST['lab_hemoglobina'];
    $creatinina = $_POST['lab_creatinina'];

    $sql = "UPDATE pacienti SET 
            telefon='$nume_tel', email='$nume_email', adresa='$adr',
            grup_sangvin='$grup_post', greutate='$greutate', inaltime='$inaltime', 
            ocupatie='$ocupatie', alergii='$alergii', antecedente_personale='$ant_pers', 
            antecedente_familie='$ant_fam', factori_mediu='$factori',
            tensiune_arteriala='$ta', alura_ventriculara='$av', auscultatie_cardio='$auscultatie', observatii_ekg='$ekg',
            lab_glicemie='$glicemie', lab_cholesterol='$chol', lab_hemoglobina='$hgb', lab_creatinina='$creatinina'
            WHERE id=$id";

    if (mysqli_query($conn, $sql)) {
        $mesaj = "<div class='alert-success'>✅ Dosar medical complet actualizat!</div>";
    } else {
        $mesaj = "<div class='alert-error'>❌ Eroare: " . mysqli_error($conn) . "</div>";
    }
}

// 1. reload date administrative
$query = "SELECT * FROM pacienti WHERE id = $id";
$rezultat = mysqli_query($conn, $query);
$p = mysqli_fetch_assoc($rezultat);

// 2. extract boli concomitente
$sql_boli = "SELECT DISTINCT diagnostic FROM consultatii WHERE pacient_id = $id AND diagnostic != ''";
$res_boli = mysqli_query($conn, $sql_boli);
$boli_lista = [];
while($row_b = mysqli_fetch_assoc($res_boli)) { $boli_lista[] = $row_b['diagnostic']; }

// 3. extract schmea tratament
$sql_scheme = "SELECT data_consult, tratament FROM consultatii 
               WHERE pacient_id = $id AND tratament IS NOT NULL AND tratament != '' 
               ORDER BY data_consult DESC LIMIT 3";
$res_scheme = mysqli_query($conn, $sql_scheme);

$grupe_sangvine = ["0 I Pozitiv", "0 I Negativ", "A II Pozitiv", "A II Negativ", "B III Pozitiv", "B III Negativ", "AB IV Pozitiv", "AB IV Negativ"];
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Dosar Complet: <?php echo htmlspecialchars($p['nume']); ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        .grid-dosar { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        .sectiune-dosar { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .sectiune-dosar h3 { border-bottom: 2px solid #0984e3; padding-bottom: 8px; margin-top: 0; color: #0984e3; display: flex; align-items: center; gap: 10px; }
        textarea, select, input { width: 100%; border: 1px solid #ddd; border-radius: 5px; padding: 8px; font-family: inherit; margin-top: 5px; box-sizing: border-box; }
        .label-blue { color: #0984e3; font-weight: bold; }
        .btn-update { background: #27ae60; color: white; border: none; padding: 15px; border-radius: 5px; cursor: pointer; font-weight: bold; width: 100%; font-size: 16px; transition: 0.3s; margin-top: 10px; }
        .lab-input-group { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .unitate-masura { font-size: 11px; color: #7f8c8d; }
        label { display: block; margin-top: 10px; font-weight: 600; color: #34495e; font-size: 13px; }
        .alert-success { background: #dff9fb; color: #0984e3; padding: 15px; border-radius: 6px; margin-bottom: 20px; border: 1px solid #74b9ff; text-align: center; }
        .status-durere { padding: 3px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
        .sumar-automat { background: #fffbe6; border-left: 5px solid #f1c40f; }
        .tag-boala { display: inline-block; background: #f39c12; color: white; padding: 2px 8px; border-radius: 15px; font-size: 12px; margin: 2px; }
    </style>
</head>
<body>

<?php include('sidebar.php'); ?>

<div class="main-content">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h1>📋 Dosar Medical: <?php echo htmlspecialchars($p['nume'] . " " . $p['prenume']); ?></h1>
        <a href="consultatie_noua.php?id=<?php echo $id; ?>" style="text-decoration: none; background: #e67e22; color:white; padding: 10px 20px; border-radius: 5px; font-weight: bold;">🩺 Consult Nou</a>
    </div>

    <?php echo $mesaj; ?>

    <form method="POST">
        <div class="grid-dosar">
            <div class="sectiune-dosar">
                <h3>🆔 1. Identitate & Contact</h3>
                <p><span class="label-blue">CNP:</span> <?php echo htmlspecialchars($p['cnp']); ?></p>
                <p><span class="label-blue">Data Nașterii:</span> <?php echo htmlspecialchars($p['data_nasterii']); ?></p>
                
                <label>Telefon</label>
                <input type="text" name="telefon" value="<?php echo htmlspecialchars($p['telefon']); ?>">
                
                <label>Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($p['email']); ?>">

                <label>Grup Sangvin</label>
                <select name="grup_sangvin">
                    <option value="">-- Selectează --</option>
                    <?php foreach($grupe_sangvine as $g): ?>
                        <option value="<?php echo $g; ?>" <?php echo ($p['grup_sangvin'] == $g) ? 'selected' : ''; ?>><?php echo $g; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="sectiune-dosar">
                <h3>⚖️ 2. Biometrie & Adresă</h3>
                <div class="lab-input-group">
                    <div><label>Greutate (kg)</label><input type="number" step="0.1" name="greutate" value="<?php echo $p['greutate']; ?>"></div>
                    <div><label>Înălțime (cm)</label><input type="number" name="inaltime" value="<?php echo $p['inaltime']; ?>"></div>
                </div>
                <label>Ocupație</label>
                <input type="text" name="ocupatie" value="<?php echo htmlspecialchars($p['ocupatie']); ?>">
                <label>Adresă Domiciliu</label>
                <textarea name="adresa" rows="2"><?php echo htmlspecialchars($p['adresa']); ?></textarea>
            </div>
        </div>

        <div class="sectiune-dosar" style="margin-bottom:20px; border-left: 5px solid #ff7675;">
            <h3 style="color:#d63031;">⚠️ 3. Alergii & Anamneză</h3>
            <label style="color: #d63031;">Alergii Cunoscute</label>
            <textarea name="alergii" rows="2"><?php echo htmlspecialchars($p['alergii']); ?></textarea>
            <div class="grid-dosar" style="margin-top:10px;">
                <div><label>Antecedente Personale</label><textarea name="ant_personale" rows="4"><?php echo htmlspecialchars($p['antecedente_personale']); ?></textarea></div>
                <div><label>Antecedente Familie</label><textarea name="ant_familie" rows="4"><?php echo htmlspecialchars($p['antecedente_familie']); ?></textarea></div>
            </div>
            <label>Factori de mediu / Stil de viață</label>
            <textarea name="factori_mediu" rows="1"><?php echo htmlspecialchars($p['factori_mediu']); ?></textarea>
        </div>

        <div class="sectiune-dosar sumar-automat" style="margin-bottom:20px;">
            <h3 style="color: #856404;">📋 Sumar Clinic Dinamic (din istoricul consultărilor)</h3>
            <div class="grid-dosar">
                <div>
                    <label style="color: #856404;">🩺 Diagnostice înregistrate / Boli concomitente:</label>
                    <div style="margin-top:10px;">
                        <?php if(!empty($boli_lista)): ?>
                            <?php foreach($boli_lista as $boala): ?>
                                <span class="tag-boala"><?php echo htmlspecialchars($boala); ?></span>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p style="font-style: italic; color: #7f8c8d;">Niciun diagnostic anterior înregistrat.</p>
                        <?php endif; ?>
                    </div>
                </div>
                <div>
                    <label style="color: #004085;">💊 Scheme de tratament recente:</label>
                    <div style="margin-top:5px;">
                        <?php if(mysqli_num_rows($res_scheme) > 0): ?>
                            <?php while($s = mysqli_fetch_assoc($res_scheme)): ?>
                                <div style="background: white; padding: 8px; border-radius: 5px; border: 1px dashed #004085; margin-bottom: 8px; font-size: 12px;">
                                    <strong>📅 <?php echo date('d.m.Y', strtotime($s['data_consult'])); ?>:</strong><br>
                                    <?php echo nl2br(htmlspecialchars($s['tratament'])); ?>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p style="font-style: italic; color: #7f8c8d;">Niciun tratament prescris anterior.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid-dosar">
            <div class="sectiune-dosar" style="border-left: 5px solid #0984e3;">
                <h3>🩺 5. Examen Clinic Obiectiv</h3>
                <div class="lab-input-group">
                    <div><label>Tensiune Art. (mmHg)</label><input type="text" name="tensiune_arteriala" value="<?php echo htmlspecialchars($p['tensiune_arteriala']); ?>"></div>
                    <div><label>Alură Ventr. (bpm)</label><input type="number" name="alura_ventriculara" value="<?php echo $p['alura_ventriculara']; ?>"></div>
                </div>
                <label>Auscultație (Cord/Pulmonar)</label>
                <textarea name="auscultatie_cardio" rows="2"><?php echo htmlspecialchars($p['auscultatie_cardio']); ?></textarea>
                <label>Observații EKG</label>
                <textarea name="observatii_ekg" rows="2"><?php echo htmlspecialchars($p['observatii_ekg']); ?></textarea>
            </div>

            <div class="sectiune-dosar" style="border-left: 5px solid #6c5ce7;">
                <h3>🧪 6. Laborator (Analize)</h3>
                <div class="lab-input-group">
                    <div><label>Glicemie (mg/dL)</label><input type="number" step="0.1" name="lab_glicemie" value="<?php echo $p['lab_glicemie']; ?>"></div>
                    <div><label>Cholesterol (mg/dL)</label><input type="number" step="0.1" name="lab_cholesterol" value="<?php echo $p['lab_cholesterol']; ?>"></div>
                </div>
                <div class="lab-input-group">
                    <div><label>Hemoglobină (g/dL)</label><input type="number" step="0.1" name="lab_hemoglobina" value="<?php echo $p['lab_hemoglobina']; ?>"></div>
                    <div><label>Creatinină (mg/dL)</label><input type="number" step="0.1" name="lab_creatinina" value="<?php echo $p['lab_creatinina']; ?>"></div>
                </div>
            </div>
        </div>

        <button type="submit" name="actualizeaza_dosar" class="btn-update">💾 ACTUALIZEAZĂ DOSARUL MEDICAL COMPLET</button>
    </form>

    <div class="sectiune-dosar" style="margin-top:20px;">
        <h3>📜 4. Istoric Vizite / Consultații</h3>
        <table width="100%" style="border-collapse: collapse; margin-top: 10px;">
            <thead>
                <tr style="background:#f8f9fa; text-align:left; border-bottom: 2px solid #eee;">
                    <th style="padding:12px;">Data</th>
                    <th>Diagnostic</th>
                    <th>Simptomatologie / Durere</th>
                    <th>Acțiuni</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $q_cons = "SELECT * FROM consultatii WHERE pacient_id = $id ORDER BY data_consult DESC";
                $res_cons = mysqli_query($conn, $q_cons);
                
                if(mysqli_num_rows($res_cons) > 0) {
                    while($c = mysqli_fetch_assoc($res_cons)) {
                        $intensitate = $c['durere_intensitate'];
                        $culoare_durere = ($intensitate > 7) ? '#eb4d4b' : (($intensitate > 4) ? '#f0932b' : '#6ab04c');
                        echo "<tr style='border-bottom:1px solid #eee;'>
                                <td style='padding:12px;'>" . date('d.m.Y', strtotime($c['data_consult'])) . "</td>
                                <td><span style='font-weight:bold; color:#2d3436;'>".htmlspecialchars($c['diagnostic'])."</span></td>
                                <td>
                                    <span class='status-durere' style='background: $culoare_durere; color: white;'>
                                        VAS: {$intensitate}/10
                                    </span>
                                </td>
                                <td>
                                    <a href='vezi_consultatie.php?id={$c['id']}' style='color:#0984e3; text-decoration:none; font-weight:bold;'>👁️ Vezi Detalii</a>
                                </td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='4' style='padding:20px; text-align:center; color:#95a5a6;'>Nicio consultație înregistrată pentru acest pacient.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>