<?php
session_start();
require '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT u.*, r.libelle role
                           FROM users u
                           JOIN roles r ON u.id_role = r.id_role
                           WHERE email = ?");
    $stmt->execute([$email]);

    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['mot_de_passe'])) {

        $_SESSION['user_id'] = $user['id_user'];
        $_SESSION['nom'] = $user['nom'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] === 'chef_projet') {
            header("Location: ../dashboard/cdp.php");
        } else {
            header("Location: ../dashboard/membre.php");
        }
        exit();

    } else {
        $error = "Identifiants incorrects";
    }
}
?>

<?php include '../includes/header.php'; ?>

<form method="POST">
  <h3>Login</h3>

  <?php if(isset($error)) echo $error; ?>

  <input type="email" name="email" required>
  <input type="password" name="password" required>

  <button>Connexion</button>
</form>

</div></body></html>
