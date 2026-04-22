<?php
// Archivo: Heroe.php
require_once 'Personaje.php';
class Heroe extends Personaje {
    public function __construct($nombre, $imagen) {
        parent::__construct($nombre, 70, 50, 20, 10, $imagen, 'normal');
    }
}