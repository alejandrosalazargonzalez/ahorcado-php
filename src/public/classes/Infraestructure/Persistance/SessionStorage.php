<?php
/**
 * @author alejandrosalazargonzalez
 * @version 1.0.0
 */

namespace classes;
class SessionStorage {
    private string $key;

    /**
     * Inicializa sesión y espacio de datos.
     */
    public function __construct(string $key = "hangman") {
        $this->key = $key;
    }

    /**
     * Guarda valor.
     */
    public function set(string $name, $value): void {
        $_SESSION[$this->key][$name] = $value;
    }

    /**
     * Recupera valor o $default.
     */
    public function get(string $name) {
        return $_SESSION[$this->key][$name] ?? null;
    }

    /**
     * Resetea el almacenamiento de la sesión
     */
    public function reset(): void {
        unset($_SESSION[$this->key]);
    }
}
