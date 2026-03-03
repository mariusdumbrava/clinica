<?php
include('config.php');

if (!isset($_SESSION['medic_id'])) {
    header("Location: index.php");
    exit();
}

$medic_id = $_SESSION['medic_id'];
$data_azi = date('Y-m-d');

// 1. Total pacienti
$res_p = mysqli_query($conn, "SELECT COUNT(*) as total FROM pacienti");
$total_pacienti = mysqli_fetch_assoc($res_p)['total'];

// 2. Programari azi
$res_prog_azi = mysqli_query($conn, "SELECT COUNT(*) as total FROM programari WHERE data_programare = '$data_azi' AND medic_id = $medic_id AND status = 'activ'");
$total_azi = mysqli_fetch_assoc($res_prog_azi)['total'];

// 3. Programarile de astăzi (JOIN cu pacienți)
$sql_agenda = "SELECT pr.*, pa.nume, pa.prenume, pa.telefon 
               FROM programari pr 
               JOIN pacienti pa ON pr.pacient_id = pa.id 
               WHERE pr.data_programare = '$data_azi' 
               AND pr.medic_id = $medic_id
               AND pr.status = 'activ'
               ORDER BY pr.ora_programare ASC";
$res_agenda = mysqli_query($conn, $sql_agenda);

// 4. Ultimele consultatii finalizate
$sql_recent = "SELECT c.*, p.nume, p.prenume 
               FROM consultatii c 
               JOIN pacienti p ON c.pacient_id = p.id 
               ORDER BY c.id DESC LIMIT 3";
$res_recent = mysqli_query($conn, $sql_recent);
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Clinic Pro</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border-top: 4px solid #0984e3; }
        .stat-card.urgent { border-top-color: #e67e22; }
        .stat-number { display: block; font-size: 28px; font-weight: bold; color: #2d3436; margin-top: 10px; }
        
        .dashboard-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }
        .card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .card h3 { margin-top: 0; display: flex; align-items: center; gap: 10px; color: #0984e3; }
        
        .btn-start { background: #27ae60; color: white; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 13px; font-weight: bold; }
        .btn-start:hover { background: #219150; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { text-align: left; color: #636e72; font-size: 13px; padding: 10px; border-bottom: 2px solid #f1f2f6; }
        td { padding: 12px 10px; border-bottom: 1px solid #f1f2f6; font-size: 14px; }
        .ora-badge { background: #e3f2fd; color: #0984e3; padding: 3px 8px; border-radius: 4px; font-weight: bold; }
    </style>
</head>
<body>

<?php include('sidebar.php'); ?>

<div class="main-content">
    <div class="header-msg">
        <h1>Bună ziua, Dr. <?php echo explode(' ', $_SESSION['nume_medic'])[0]; ?>! 👋</h1>
        <p>Astăzi este <strong><?php echo date('d.m.Y'); ?></strong>. Iată sumarul zilei de lucru:</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <small>Total Pacienți Registru</small>
            <span class="stat-number">👥 <?php echo $total_pacienti; ?></span>
        </div>
        <div class="stat-card urgent">
            <small>Programări Astăzi</small>
            <span class="stat-number">📅 <?php echo $total_azi; ?></span>
        </div>
    </div>

    <div class="dashboard-grid">
        <div class="card">
            <h3>🕒 Agenda Zilei (Programări)</h3>
            <table>
                <thead>
                    <tr>
                        <th>Ora</th>
                        <th>Pacient</th>
                        <th>Motiv Vizită</th>
                        <th>Acțiune</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($res_agenda) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($res_agenda)): ?>
                        <tr>
                            <td><span class="ora-badge"><?php echo substr($row['ora_programare'], 0, 5); ?></span></td>
                            <td><strong><?php echo $row['nume'] . " " . $row['prenume']; ?></strong><br><small><?php echo $row['telefon']; ?></small></td>
                            <td><?php echo $row['motiv'] ? $row['motiv'] : '-'; ?></td>
                            <td>
                                <a href="consultatie_noua.php?id=<?php echo $row['pacient_id']; ?>" class="btn-start">🩺 Începe Consult</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 30px; color: #b2bec3;">
                                Nu aveți programări active pentru astăzi.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="card">
            <h3 style="color: #636e72;">📜 Ultimele Vizite</h3>
            <div style="margin-top: 15px;">
                <?php while($c = mysqli_fetch_assoc($res_recent)): ?>
                    <div style="border-bottom: 1px solid #f1f2f6; padding-bottom: 10px; margin-bottom: 10px;">
                        <small style="color: #0984e3;"><?php echo date('d.m.Y', strtotime($c['data_consult'])); ?></small><br>
                        <strong><?php echo $c['nume'] . " " . $c['prenume']; ?></strong><br>
                        <span style="font-size: 12px; color: #636e72;">Dg: <?php echo $c['diagnostic']; ?></span>
                    </div>
                <?php endwhile; ?>
                <a href="lista_consultatii.php" style="display: block; text-align: center; font-size: 13px; color: #0984e3; text-decoration: none; margin-top: 10px;">Vezi tot istoricul</a>
            </div>
        </div>
    </div>
</div>

</body>
</html>