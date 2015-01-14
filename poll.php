<?php include('header.php'); ?>

<?php
$poll = false;
if (isset($_GET['id'])) {
    $query = $pdo->prepare('SELECT * FROM polls WHERE id=:id');
    $query->bindParam(":id", $_GET['id']);
    $query->execute();
    if ($query->rowCount()) {
        $poll = $query->fetch();
    }        
}

if (!$poll) {
?>
<h2>Sondage</h2>

<div class="alert alert-danger">
    Sondage non trouvé.
</div>
<?php
} else {

$userAnswered = false;
$answers = null;

if ($currentUser) {
    $query = $pdo->prepare('SELECT * FROM answers WHERE poll_id=:pollId AND user_id=:userId');
    $query->execute(array('pollId'=>$poll['id'],'userId'=>$currentUser['id'] ));
    if ($query->rowCount()) {
        $userAnswered = true;
    } else {
        if ($_SERVER['REQUEST_METHOD']=='POST' && !empty($_POST['answer']) && ($_POST['answer']=='1' || $_POST['answer']=='2' || $_POST['answer']=='3')) {
            $query = $pdo->prepare('INSERT INTO answers (user_id,poll_id,answer) VALUES (?,?,?)');
            $query->execute(array($currentUser['id'], $poll['id'], $_POST['answer']));

            $userAnswered = true;
        }
    }

    if ($userAnswered) {
        $answers = array();
        foreach (array(1,2,3) as $answer) {
            $sql='SELECT COUNT(*) as nb FROM answers WHERE poll_id=:pollId AND answer=:answer';
            $query = $pdo->prepare($sql);
            $query->execute(array('pollId'=>$poll['id'],'answer'=>$answer));
            $res=$query->fetch();

            $answers[$answer] = $res['nb'];
        }
        $total = array_sum($answers);
    }
}

?>

<h2><?php echo $poll['question']; ?></h2>

<form method="post">

<?php foreach (array(1,2,3) as $answer) { ?>

<?php if (!$poll['answer'.$answer]) continue; ?>

<h3>
    <label>
    <?php if ($currentUser && !$userAnswered) { ?>
    <input type="radio" name="answer" value="<?php echo $answer; ?>" />
    <?php } ?>
    <?php echo $poll['answer'.$answer]; ?>
    </label>
</h3>

<?php
if ($answers) {
$pct = round($answers[$answer]*100/$total);

?>

<div class="progress">
<div class="progress-bar" role="progressbar" aria-valuenow="<?php echo $pct; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $pct; ?>%;">
  <?php echo $pct; ?>%
  </div>
</div>

<?php } ?>
<?php } ?>

<?php if ($currentUser) { 
    if (!$userAnswered) { 
?>
    <input class="btn btn-success pull-right" type="submit" value="Participer!" />
<?php
}
} else {
?>
<div class="alert alert-warning">
Vous devez être identifié pour participer!
</div>
<?php
} 
?>
</form>

<?php } ?>

<?php include('footer.php'); ?>
