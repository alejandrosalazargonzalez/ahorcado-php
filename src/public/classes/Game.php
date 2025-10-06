<?php
/**
 * @author alejandrosalazargonzalez
 * @version 1.0.0
 * toda la logica del juego
 */
namespace classes;
class Game {
    private string $palabra;
    private int $intentosMaximos;
    private int $intentosRestantes;
    private array $letrasUsadas;

    /**
     * Inicializa o restaura estado
     */
    public function __construct(string $palabra, int $intentosMaximos = 6, array $state = null) {
        if ($state) {
            $this->palabra = $state['palabra'];
            $this->intentosMaximos = $state['intentosMaximos'];
            $this->intentosRestantes = $state['intentosRestantes'];
            $this->letrasUsadas = $state['letrasUsadas'];
        } else {
            $this->palabra = strtoupper($palabra);
            $this->intentosMaximos = $intentosMaximos;
            $this->intentosRestantes = $intentosMaximos;
            $this->letrasUsadas = [];
        }
    }

    /**
     * Procesa una letra, resta intentos si falla
     */
    public function guess(string $letter): bool {
        $letter = strtoupper($letter);
        if (in_array($letter, $this->letrasUsadas)) {
            return false;
        }
        $this->letrasUsadas[] = $letter;

        if (!str_contains($this->palabra, $letter)) {
            $this->intentosRestantes--;
            return false;
        }
        return true;
    }

    /**
     * Devuelve la palabra con guiones bajos y letras descubiertas
     */
    public function getMaskedWord(): string {
        $masked = '';
        foreach (str_split($this->palabra) as $char) {
            $masked .= in_array($char, $this->letrasUsadas) ? $char : '_';
        }
        return $masked;
    }

    /**
     * Verdadero si no quedan intentos y no ganó
     */
    public function isWon(): bool {
        return $this->getMaskedWord() === $this->palabra;
    }

    /**
     * Verdadero si no quedan intentos y no ganó
     */
    public function isLost(): bool {
        return $this->intentosRestantes <= 0;
    }

    /**
     * Retorna intentos restantes
     */
    public function getAttemptsLeft(): int {
        return $this->intentosRestantes;
    }

    /**
     * Retorna letras ya jugadas
     */
    public function getUsedLetters(): array {
        return $this->letrasUsadas;
    }

    /**
     * Serializa estado (para guardar en sesión)
     */
    public function toState(): array {
        return [
            'palabra' => $this->palabra,
            'intentosMaximos' => $this->intentosMaximos,
            'intentosRestantes' => $this->intentosRestantes,
            'letrasUsadas' => $this->letrasUsadas
        ];
    }
}
