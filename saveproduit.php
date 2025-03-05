<?php
$host = "localhost";
$user = "root"; // Remplacez par votre utilisateur MySQL
$password = ""; // Remplacez par votre mot de passe MySQL
$database = "catalogue";

// Connexion à la base de données
$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}

// Enregistrer un produit avec une image
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = $_POST["nom"];
    $description = $_POST["description"];
    $prix = $_POST["prix"];
    $imagePath = ""; 

    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $fileName = basename($_FILES["image"]["name"]);
        $imagePath = $targetDir . uniqid() . "_" . $fileName;

        // Vérifier et déplacer le fichier téléchargé
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $imagePath)) {
            $sql = "INSERT INTO produits (nom, description, prix, image) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssds", $nom, $description, $prix, $imagePath);

            if ($stmt->execute()) {
                echo "<p style='color: green;'>Produit ajouté avec succès !</p>";
            } else {
                echo "<p style='color: red;'>Erreur : " . $conn->error . "</p>";
            }
            $stmt->close();
        } else {
            echo "<p style='color: red;'>Erreur lors du téléchargement de l'image.</p>";
        }
    }
}

// Récupérer les produits
$sql = "SELECT * FROM produits";
$result = $conn->query($sql);
$produits = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $produits[] = $row;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catalogue de Produits</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
        }
        header {
            background: linear-gradient(135deg, #010f1d, #004080);
            color: white;
            padding: 15px;
            text-transform: uppercase;
        }
        .product-grid {
            display: flex;
            justify-content: center;
            gap: 20px;
            padding: 20px;
            flex-wrap: wrap;
        }
        .product-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            width: 250px;
            text-align: center;
        }
        .product-card img {
            max-width: 100%;
            border-radius: 10px;
        }
        .price {
            font-size: 18px;
            font-weight: bold;
            color: #28a745;
            display: block;
            margin: 10px 0;
        }
        form {
            margin: 20px auto;
            padding: 20px;
            max-width: 400px;
            background: #f4f4f4;
            border-radius: 10px;
        }
        input, textarea {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            background-color: #010f1d;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
        }
        .preview-container {
            text-align: center;
            margin: 10px 0;
        }
        #imagePreview {
            max-width: 100%;
            height: auto;
            display: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>

<header>
    <h1>Nos Produits</h1>
</header>

<main>
    <h2>Ajouter un Produit</h2>
    <form method="POST" enctype="multipart/form-data">
        <input type="text" name="nom" placeholder="Nom du produit" required>
        <textarea name="description" placeholder="Description" required></textarea>
        <input type="number" step="0.01" name="prix" placeholder="Prix" required>
        
        <div class="preview-container">
            <img id="imagePreview" src="" alt="Prévisualisation de l'image">
        </div>

        <input type="file" name="image" id="imageInput" accept="image/*" required>
        <button type="submit">Ajouter</button>
    </form>

    <h2>Catalogue des Produits</h2>
    <div class="product-grid">
        <?php foreach ($produits as $produit) : ?>
            <div class="product-card">
                <img src="<?= htmlspecialchars($produit['image']) ?>" alt="<?= htmlspecialchars($produit['nom']) ?>">
                <h2><?= htmlspecialchars($produit['nom']) ?></h2>
                <p><?= htmlspecialchars($produit['description']) ?></p>
                <span class="price"><?= number_format($produit['prix'], 2) ?>€</span>
            </div>
        <?php endforeach; ?>
    </div>
</main>

<script>
document.getElementById("imageInput").addEventListener("change", function(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById("imagePreview");
            preview.src = e.target.result;
            preview.style.display = "block";
        };
        reader.readAsDataURL(file);
    }
});
</script>

</body>
</html>
