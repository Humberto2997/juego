<?php
// Archivo: Enemigo.php
require_once 'Personaje.php';
class Enemigo extends Personaje {
    public function __construct($nombre, $imagen, $debilidad) {
        parent::__construct($nombre, 50, 30, 15, 5, $imagen, $debilidad);
    }
}