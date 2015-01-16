<?php

ini_set('date.timezone', 'Europe/Paris');

$loader = include('vendor/autoload.php');
$loader->add('', 'src');

$app = new Silex\Application;
$app->register(new Silex\Provider\SessionServiceProvider());
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views'
));

$app['model'] = new sondage\Model(
    '127.0.0.1',  // Hôte
    'sondage',    // Base de données
    'sondage',    // Utilisateur
    'sondage'     // Mot de passe
);

// Page d'accueil
$app->match('/', function() use ($app) {
    return $app['twig']->render('index.html.twig', array(
        "session" => $app['session']->get('user')
    ));
})->bind('index');


//Déconnexion de l'utilisateur
$app->match('/deconnexion', function() use ($app) {
    $app['session']->clear();
    return $app->redirect('/');
})->bind('deconnexion');


//Formulaire connexion
$app->match('/identification', function() use ($app) {
    return $app['twig']->render('identification.html.twig', array(
        "session" => $app['session']->get('user')
    ));
})->bind('identification');


//Connexion
$app->post('/checkUser', function() use ($app) {
    $login = htmlspecialchars($_POST['login']);
    $password = htmlspecialchars($_POST['password']);
    $msgSuccess = null;
    $msgErreurLogin = true;
    $msgErreurMdp = true;

    if ($login != null && $password != null) { 
        if($app['model']->checkLogin($login) == true){
            $msgErreurLogin = null;
            if($app['model']->checkConnexion($login,$password) == true) {
                $msgErreurMdp = null;
                $idUser = $app['model']->getIdUserByLogin($login);
                $msgSuccess = true;
                $msgErreurMdp = null;
                $app['session']->set('user', array(
                    'idUser' => $idUser,
                    'login' => $login
                ));
            }
        }
    }
    return $app['twig']->render('identification.html.twig', array(
        "session" => $app['session']->get('user'),
        "msgErreurLogin" => $msgErreurLogin,
        "msgErreurMdp" => $msgErreurMdp,
        "msgSuccess" => $msgSuccess,
        "login" => $login
    ));
})->bind('checkUser');


//Formulaire création de sondage
$app->match('/nouveauSondage', function() use ($app) {
    return $app['twig']->render('nouveauSondage.html.twig', array(
        "session" => $app['session']->get('user')
    ));
})->bind('nouveauSondage');


//Ajout de sondage
$app->post('/creationSondage', function() use ($app) {

    $question = htmlspecialchars($_POST['question']);
    $answer1 = htmlspecialchars($_POST['answer1']);
    $answer2 = htmlspecialchars($_POST['answer2']);;
    $answer3 = htmlspecialchars($_POST['answer3']);
    $user = $app['session']->get('user');
    $idCreateur = $user['idUser'];
    $msgErreurChamps = true;
    $msgSuccess = null;

    if (!empty($question) && !empty($answer1) && !empty($answer2) && isset($answer3)) {
        $msgErreurChamps = null;
        $app['model']->addSondage($question, $answer1, $answer2, $answer3, $idCreateur);
        $msgSuccess = true;
    }

    return $app['twig']->render('nouveauSondage.html.twig', array(
        "session" => $app['session']->get('user'),
        "msgErreurChamps" => $msgErreurChamps,
        "msgSuccess" => $msgSuccess
    ));

})->bind('creationSondage');


//Formulaire d'inscription
$app->match('/inscription', function() use ($app) {
    return $app['twig']->render('inscription.html.twig', array(
        "session" => $app['session']->get('user')
    ));
})->bind('inscription');


$app->post('/creationCompte', function() use ($app) {

    $login = htmlspecialchars($_POST['login']);
    $password = htmlspecialchars($_POST['password']);
    $msgErreurChamps = true;
    $msgSuccess = null;
    $msgErreurLogin = true;

    if ($login != null && $password != null) {
        $msgErreurChamps = null;
        if($app['model']->checkLogin($login) == false){
            $msgErreurLogin = null;
            $app['model']->addUser($login, $password);
            $msgSuccess = true;
        }
    }

    return $app['twig']->render('inscription.html.twig', array(
        "session" => $app['session']->get('user'),
        "msgErreurChamps" => $msgErreurChamps,
        "login" => $login,
        "msgSuccess" => $msgSuccess,
        "msgErreurLogin" => $msgErreurLogin
    ));

})->bind('creationCompte');


//liste des sondages
$app->match('/sondages', function() use ($app) {

    return $app['twig']->render('sondages.html.twig', array(
        "session" => $app['session']->get('user'),
        "sondages" => $app['model']->getListSondages()
    ));
})->bind('sondages');


$app->match('/sondage/{numero}', function($numero) use ($app) {
    $idSondage = $numero;
    $user = $app['session']->get('user');
    $idUser = $user['idUser'];

    $userAnswered = true;
    
    if ($app['model']->checkUserAnswered($idSondage, $idUser) == true){
        $userAnswered = null;
    }

    return $app['twig']->render('sondage.html.twig', array(
        "session" => $app['session']->get('user'),
        "userAnswered" => $userAnswered,
        "reponses" => $app['model']->getListAnswers($idSondage)
    ));
})->bind('sondage');



$app->post('/ajoutReponse', function() use ($app) {
    return $app->redirect('/sondages');
})->bind('ajoutReponse');






// Fait remonter les erreurs
$app->error(function($error) {
    throw $error;
});

$app->run();