<?php
include('config.php');

if (!isset($_SESSION['medic_id'])) {
    header("Location: index.php");
    exit();
}

// Preluam toate consultatiile
$query = "SELECT c.id, c.data_consult, c.diagnostic, c.durere_intensitate, p.nume, p.prenume, p.id AS pacient_id 
          FROM consultatii c 
          JOIN pacienti p ON c.pacient_id = p.id 
          ORDER BY c.data_consult DESC, c.id DESC";

$rezultat = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Registru Consultații - Clinic Pro</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .tabel-consultatii {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .tabel-consultatii th {
            background: #2d3436;
            color: white;
            padding: 15px;
            text-align: left;
        }
        .tabel-consultatii td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
        }
        .tabel-consultatii tr:hover {
            background-color: #f8f9fa;
        }
        .badge-durere {
            padding: 4px 8px;
            border-radius: 4px;
            color: white;
            font-size: 12px;
            font-weight: bold;
        }
    </style>
</head>
<body>

<?php include('sidebar.php'); ?>

<div class="main-content">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h1>🩺 Registru General Consultații</h1>
        <p>Total vizite înregistrate: <strong><?php echo mysqli_num_rows($rezultat); ?></strong></p>
    </div>

    <table class="tabel-consultatii">
        <thead>
            <tr>
                <th>Data</th>
                <th>Pacient</th>
                <th>Diagnostic Principal</th>
                <th>Intensitate Durere</th>
                <th>Acțiuni</th>
            </tr>
        </thead>
        <tbody>
            <?php while($c = mysqli_fetch_assoc($rezultat)): 
                // culori pentru durere
                $durere = $c['durere_intensitate'];
                $culoare = ($durere >= 7) ? '#e74c3c' : (($durere >= 4) ? '#f39c12' : '#27ae60');
            ?>
            <tr>
                <td><strong><?php echo date('d.m.Y', strtotime($c['data_consult'])); ?></strong></td>
                <td>
                    <a href="profil_pacient.php?id=<?php echo $c['pacient_id']; ?>" style="text-decoration:none; color:#2d3436; font-weight:600;">
                        <?php echo $c['nume'] . " " . $c['prenume']; ?>
                    </a>
                </td>
                <td><?php echo $c['diagnostic']; ?></td>
                <td>
                    <span class="badge-durere" style="background: <?php echo $culoare; ?>;">
                        VAS: <?php echo $durere; ?>/10
                    </span>
                </td>
                <td>
                    <a href="vezi_consultatie.php?id=<?php echo $c['id']; ?>" style="color:#0984e3; text-decoration:none; font-weight:bold;">👁️ Detalii</a>
                </td>
            </tr>
            <?php endwhile; ?>
            
            <?php if(mysqli_num_rows($rezultat) == 0): ?>
            <tr>
                <td colspan="5" style="text-align:center; padding:30px; color:#7f8c8d;">Nu există consultații înregistrate în sistem.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>