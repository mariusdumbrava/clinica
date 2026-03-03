<?php
include('config.php');

if (!isset($_SESSION['medic_id'])) {
    header("Location: index.php");
    exit();
}

// termen de cautare
$search = "";
if (isset($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
}

// Interogare SQL(Nume, Prenume sau CNP)
$query = "SELECT * FROM pacienti 
          WHERE nume LIKE '%$search%' 
          OR prenume LIKE '%$search%' 
          OR cnp LIKE '%$search%' 
          ORDER BY nume ASC";
$rezultat = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Registru Pacienți</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .search-wrapper {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            gap: 10px;
        }
        .search-input {
            flex-grow: 1;
            padding: 12px 15px;
            border: 1px solid #dfe6e9;
            border-radius: 6px;
            font-size: 16px;
            outline: none;
        }
        .search-input:focus { border-color: #0984e3; }
        .btn-search {
            background: #0984e3;
            color: white;
            border: none;
            padding: 0 25px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            transition: 0.3s;
        }
        .btn-search:hover { background: #0773c5; }
        .btn-reset {
            line-height: 45px;
            text-decoration: none;
            color: #d63031;
            font-size: 14px;
        }
        .empty-state {
            text-align: center;
            padding: 40px;
            background: #f9f9f9;
            border-radius: 10px;
            color: #636e72;
        }
        /* Stiluri pentru butoanele de acțiune */
        .action-link {
            font-weight: bold;
            text-decoration: none;
            margin-left: 10px;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
    </style>
</head>
<body>

<?php include('sidebar.php'); ?>

<div class="main-content">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>👥 Registru Pacienți</h2>
        <a href="adauga_pacient.php" class="btn-submit" style="text-decoration: none; background: #27ae60; padding: 10px 15px; color: white; border-radius: 5px; font-weight: bold;">+ Adaugă Pacient</a>
    </div>

    <form method="GET" action="lista_pacienti.php" class="search-wrapper">
        <input type="text" name="search" class="search-input" 
               placeholder="Caută după nume sau CNP..." 
               value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit" class="btn-search">Căutare</button>
        <?php if($search != ""): ?>
            <a href="lista_pacienti.php" class="btn-reset">Resetează</a>
        <?php endif; ?>
    </form>

    <?php if(mysqli_num_rows($rezultat) > 0): ?>
    <table border="0" style="width: 100%; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <thead style="background: #2d3436; color: white;">
            <tr>
                <th style="padding: 15px; text-align: left;">Nume Complet</th>
                <th>CNP</th>
                <th>Telefon</th>
                <th style="text-align: right; padding-right: 20px;">Acțiuni</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = mysqli_fetch_assoc($rezultat)) { ?>
            <tr style="border-bottom: 1px solid #eee;">
                <td style="padding: 15px;">
                    <strong><?php echo htmlspecialchars($row['nume'] . " " . $row['prenume']); ?></strong>
                </td>
                <td style="text-align: center; color: #636e72;"><?php echo htmlspecialchars($row['cnp']); ?></td>
                <td style="text-align: center;"><?php echo htmlspecialchars($row['telefon']); ?></td>
                <td style="text-align: right; padding-right: 20px;">
                    <a href="programari.php?pacient_id=<?php echo $row['id']; ?>" 
                       class="action-link" style="color: #6c5ce7;">📅 Programează</a>

                    <a href="consultatie_noua.php?id=<?php echo $row['id']; ?>" 
                       class="action-link" style="color: #e67e22;">🩺 Consult Nou</a>
                    
                    <a href="profil_pacient.php?id=<?php echo $row['id']; ?>" 
                       class="action-link" style="color: #0984e3;">👁️ Dosar</a>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
    <?php else: ?>
        <div class="empty-state">
            <p>Nu s-au găsit pacienți pentru: <strong>"<?php echo htmlspecialchars($search); ?>"</strong></p>
            <a href="lista_pacienti.php" style="color: #0984e3;">Înapoi la lista completă</a>
        </div>
    <?php endif; ?>
</div>

</body>
</html>