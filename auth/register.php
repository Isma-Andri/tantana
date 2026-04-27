<?php
require '../config/db.php';
require '../includes/functions.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nom = sanitize($_POST['nom']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO users (nom, email, mot_de_passe, id_role)
                           VALUES (?, ?, ?, ?)");
    try {
        $stmt->execute([$nom, $email, $hash, $role]);
        redirect('login.php');
    } catch (PDOException $e) {
        $error = "Email déjà utilisé";
    }
}
?>

<?php include '../includes/header.php'; ?>

<form method="POST">
  <h3>Register</h3>

  <?php if(isset($error)) echo $error; ?>

  <input type="text" name="nom" placeholder="Nom" required>
  <input type="email" name="email" placeholder="Email" required>
  <input type="password" name="password" required>

  <select name="role">
    <option value="1">Chef de projet</option>
    <option value="2">Membre</option>
  </select>

  <button>Créer</button>
</form>

</div></body></html>
