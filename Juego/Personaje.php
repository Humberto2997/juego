<?php
abstract class Personaje {
    public $nombre, $vida, $energia, $ataque, $defensa, $imagen, $tipoDebilidad;

    public function __construct($nombre, $vida, $energia, $ataque, $defensa, $imagen, $tipoDebilidad) {
        $this->nombre = $nombre;
        $this->vida = $vida;
        $this->energia = $energia;
        $this->ataque = $ataque;
        $this->defensa = $defensa;
        $this->imagen = $imagen;
        $this->tipoDebilidad = $tipoDebilidad;
    }

    public function calcularAtaqueRandom($tipo, $objetivo) {
        if (rand(1, 100) > 80) return 0; // 20% de fallo
        $dañoBase = rand($this->ataque / 2, $this->ataque);
        if ($tipo === $objetivo->tipoDebilidad) { $dañoBase *= 1.5; }
        return (int)$dañoBase;
    }
}