<?php include('header.php'); ?>

<h2>Inscription</h2>

<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (!empty($_POST['login']) && !empty($_POST['password'])) {
            $login = htmlspecialchars($_POST['login']);
            $query = $pdo->prepare('SELECT COUNT(*) as nb FROM users WHERE login=:login');
            $query->bindParam(":login", $login);
            $query->execute();
            $res = $query->fetch();

            if ($res['nb'] == 0) {
                $login = htmlspecialchars($_POST['login']);
                $password = htmlspecialchars($_POST['password']);
                $query = $pdo->prepare('INSERT INTO users (login, password) VALUES (?,?)');
                $query->execute(array($login, md5($password)));
?>
<div class="alert alert-success">
    Félicitations! Vous êtes désormais inscrits.
</div>
<?php
            } else {
?>
<div class="alert alert-danger">
    L'utilisateur <?php echo htmlspecialchars($_POST['login']); ?> existe déjà.
</div>
<?php
            }
        } else {
?>
<div class="alert alert-danger">
    Vous devez renseigner tous les champs.
</div>
<?php
        }
    }
?>

<form method="post" class="form-horizontal">
    <div class="form-group">
        <label class="col-sm-2" for="login">Identifiant</label>
        <div class="col-sm-10">
            <input required="required" class="form-control" type="text" id="login" name="login" />
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2" for="password">Mot de passe</label>
        <div class="col-sm-10">
            <input required="required" class="form-control" type="password" id="password" name="password" />
        </div>
    </div>
    <div class="form-group">
        <div class="col-sm-2">&nbsp;</div>
        <div class="col-sm-10">
            <input type="submit" class="btn btn-success" value="Enregistrer" />
        </div>
    </div>
</form>

<?php include('footer.php'); ?>
