<?php

namespace Controllers;

class AdminController
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function panelAdministracion()
    {
        // Solo usuarios autenticados pueden acceder al panel
        echo $twig->render('admin.html.twig');
    }
}
