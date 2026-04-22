<?php
class Procesador {
    public $nombre;
    public $email;
    public $cedula;
    public $edad;
    
    public $peso;
    public $altura;
    public $imc;
    
    public function procesarPost($datos) {
        $this->nombre = $datos['nombre'];
        $this->email = $datos['email'];
        $this->cedula = $datos['cedula'];
        $this->edad = $datos['edad'];
    }
    
    public function procesarGet($datos) {
        $this->nombre = $datos['nombre'];
        $this->peso = $datos['peso'];
        $this->altura = $datos['altura'];
        $this->imc = $this->peso / ($this->altura * $this->altura);
    }
}

$procesador = new Procesador();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $procesador->procesarPost($_POST);
    include 'salidaPOST.php';
} 
else if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $procesador->procesarGet($_GET);
    include 'salidaGET.php';
}
?>