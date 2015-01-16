<?php

namespace Sondage;

/**
 * Représente le "Model", c'est à dire l'accès à la base de
 * données pour l'application cinéma basé sur MySQL
 */
class Model {
    protected $pdo;

    public function __construct($host, $database, $user, $password)
    {
        try {
            $this->pdo = new \PDO(
                'mysql:dbname='.$database.';host='.$host,
                $user,
                $password
            );
            $this->pdo->exec('SET CHARSET UTF8');
        } catch (\PDOException $exception) {
            die('Impossible de se connecter au serveur MySQL');
        }
    }

    //renvoie true si le login et le mdp correspondent
    public function checkConnexion($login,$password)
    {
        $sql = "SELECT * FROM users WHERE users.login=:login AND users.password=:password";
        $password = md5($password);
        $query = $this->pdo->prepare($sql);
        $query->bindParam(":login",$login);
        $query->bindParam(":password", $password);
        $query->execute();
        if($query->rowCount() > 0) {
            return true;
        }
        return false;
    }

    public function getIdUserByLogin($login)
    {
        $sql = "SELECT users.id FROM users WHERE users.login = :login";
        $query = $this->pdo->prepare($sql);
        $query->bindParam(":login",$login);
        $query->execute();
        return $query->execute();

    }

    //renvoie true si le login existe
    public function checkLogin($login)
    {
        $sql = "SELECT * FROM users WHERE users.login=:login";
        $query = $this->pdo->prepare($sql);
        $query->bindParam(":login",$login);
        $query->execute();
        if($query->rowCount() > 0) {
            return true;
        }
        return false;
    }

    public function addUser($login, $password)
    {
        $password = md5($password);
        $sql = "INSERT INTO `sondage`.`users` (`login`, `password`) VALUES (:login, :password)";
        $query = $this->pdo->prepare($sql);
        $query->bindParam(":login",$login);
        $query->bindParam(":password", $password);
        return $query->execute();
    }

    public function addSondage($question, $answer1, $answer2, $answer3)
    {
        $sql = "INSERT INTO polls (question,answer1,answer2,answer3) VALUES (:question, :answer1, :answer2, :answer3)";
        $query = $this->pdo->prepare($sql);
        $query->bindParam(":question",$question);
        $query->bindParam(":answer1", $answer1);
        $query->bindParam(":answer2", $answer2);
        $query->bindParam(":answer3", $answer3);
        return $query->execute();
    }

    public function getListSondages()
    {
        $sql = "SELECT * FROM polls"; 
        $query = $this->pdo->prepare($sql);
        $query->execute();
        $results = $query->fetchAll();
        $sondages = array();
        foreach ($results as $key => $sondage) {
            $sondages[] = $sondage;
        }
        return $sondages;
    }

    //Retourne true si l'utilisateur a déjà répondu
    public function checkUserAnswered($idSondage, $idUser)
    {
        $sql = "SELECT * FROM answers WHERE poll_id=:pollId AND user_id=:userId";
        $query = $this->pdo->prepare($sql);
        $query->bindParam(":pollId",$idSondage);
        $query->bindParam(":userId",$idUser);
        $query->execute();
        if($query->rowCount() > 0) {
            return true;
        }
        return false;
    }

    public function getListAnswers($idSondage)
    {
        $sql = "SELECT polls.question, polls.answer1, polls.answer2, polls.answer3 FROM polls WHERE id=:id"; 
        $query = $this->pdo->prepare($sql);
        $query->bindParam(":id",$idSondage);
        $query->execute();
        $results = $query->fetchAll();
        $reponses = array();
        foreach ($results as $key => $reponse) {
            $reponses[] = $reponse;
        }
        return $reponses;
    }
}