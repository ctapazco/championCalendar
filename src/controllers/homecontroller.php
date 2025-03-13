<?php

namespace Controllers;

class HomeController
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function index()
    {
        $stmt = $this->pdo->prepare("SELECT * FROM equipos");
        $stmt->execute();
        $equipos = $stmt->fetchAll();

        echo $twig->render('index.html.twig', ['equipos' => $equipos]);
    }
}
