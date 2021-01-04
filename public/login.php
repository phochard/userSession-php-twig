<?php

// activation du système d'autoloading de Composer
require __DIR__ . '/../vendor/autoload.php';

// instanciation du chargeur de templates
$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../templates');

// instanciation du moteur de template
$twig = new \Twig\Environment($loader, [
    // activation du mode debug
    'debug' => true,
    // activation du mode de variables strictes
    'strict_variables' => true,
    // activation du cache, au passage en prod
    //'cache' => __DIR__ . '/../var/cache',
]);

// chargement de l'extension Twig_Extension_Debug, à enlever lors du passage en prod
$twig->addExtension(new \Twig\Extension\DebugExtension());

session_start();

$user = require __DIR__.'/user-data.php';

$formData = [
    'login' => '',
    'password_hash' => '',
];

$errors = [];
$messages = [];

if ($_POST) {

    // remplacement des valeur par défaut par celles de l'utilisateur
    if (isset($_POST['login'])) {
        $formData['login'] = $_POST['login'];
    }
    if (isset($_POST['password_hash'])) {
        $formData['password_hash'] = $_POST['password_hash'];
    }

    // validation des données envoyées par l'utilisateur
    if (empty($_POST['login']) || (strlen($_POST['login'])<4) || (strlen($_POST['login'])>100) || 
        empty($_POST['password_hash']) || (strlen($_POST['password_hash'])<4) || (strlen($_POST['password_hash'])>100)){
        $errors['size'] = true;
        $messages['size'] = "Login and password must contain between 4 and 100 caracters.";
    } elseif (!password_verify($_POST['password_hash'], $user['password_hash']) || $_POST['login'] != $user['login']) {
        $errors['invalid']= true;
        $messages['invalid'] = "Invalid Username or Password.";
    }

    // si les données entrées par l'utilisateur sont correctes
    if (!$errors) {
        $_SESSION['login'] = $user['login'];
        $_SESSION['password_hash'] = $user['password_hash'];
        $_SESSION['user_id'] = $user['user_id'];

        $url='Location:./private-page.php';
        header($url, true, 302);
        exit();
    }
}

    // affichage du rendu d'un template
echo $twig->render('login.html.twig', [
    // transmission de données au template
    'errors' => $errors,
    'messages' => $messages,
    'session' => $_SESSION,
    'formData' => $formData,
]);