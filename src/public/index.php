<?php
session_start();

class WordProvider {
    private string $filePath;

    public function __construct(string $filePath) {
        $this->filePath = $filePath;
    }

    /**
     * Retorna palabra aleatoria (mayúsculas, limpia de acentos)
     */
    public function randomWord(): string {
        $content = file_get_contents($this->filePath);
        $words = explode(",", $content);
        $words = array_map('trim', $words);
        $word = $words[array_rand($words)];
        return strtoupper($word);
    }
}

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

/**
 * dibuja el ahorcado en ASCII
 */
class Renderer {
    public function ascii(int $attemptsLeft): string {
        $stages = [
            0 => " 
              -----
              |   |
              O   |
             /|\\  |
             / \\  |
                  |
            --------
            ",
            1 => "
              -----
              |   |
              O   |
             /|\\  |
             /    |
                  |
            --------
            ",
            2 => "
              -----
              |   |
              O   |
             /|\\  |
                  |
                  |
            --------
            ",
            3 => "
              -----
              |   |
              O   |
             /|   |
                  |
                  |
            --------
            ",
            4 => "
              -----
              |   |
              O   |
              |   |
                  |
                  |
            --------
            ",
            5 => "
              -----
              |   |
              O   |
                  |
                  |
                  |
            --------
            ",
            6 => "
              -----
              |   |
                  |
                  |
                  |
                  |
            --------
            ",
        ];
        return "<pre>" . $stages[$attemptsLeft] . "</pre>";
    }
}

$storage = new SessionStorage();
$renderer = new Renderer();

/**
 * Resetear juego
 */
if (isset($_POST['reset'])) {
    $storage->reset();
    header("Location: index.php");
    exit;
}

/**
 * Restaurar o crear juego
 */
$state = $storage->get("state");
if ($state) {
    $game = new Game($state['palabra'], $state['intentosMaximos'], $state);
} else {
    $wordProvider = new WordProvider("palabras.txt");
    $word = $wordProvider->randomWord();
    $game = new Game($word);
}

/**
 * comprobar letra
 */
if (isset($_POST['letter'])) {
    $letter = $_POST['letter'];
    $game->guess($letter);
}
/**
 * guardar estado
 */
$storage->set("state", $game->toState());
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Juego del Ahorcado</title>
</head>
<body>
    <h1>Juego del Ahorcado</h1>
    <p><?php echo $game->getMaskedWord(); ?></p>
    <p>Intentos restantes: <?php echo $game->getAttemptsLeft(); ?></p>
    <p>Letras usadas: <?php echo implode(", ", $game->getUsedLetters()); ?></p>
    <?php echo $renderer->ascii($game->getAttemptsLeft()); ?>

    <?php if ($game->isWon()): ?>
        <h2>¡Ganaste!</h2>
        <form method="post">
            <button type="submit" name="reset">Jugar de nuevo</button>
        </form>
    <?php elseif ($game->isLost()): ?>
        <h2>Perdiste. La palabra era: <?php echo $state['palabra']; ?></h2>
        <form method="post">
            <button type="submit" name="reset">Jugar de nuevo</button>
        </form>
    <?php else: ?>
        <form method="post">
            <label for="letter">Introduce una letra:</label>
            <input type="text" name="letter" id="letter" maxlength="1" required>
            <button type="submit">Probar</button>
        </form>
        <form method="post">
            <button type="submit" name="reset">Reiniciar juego</button>
        </form>
    <?php endif; ?>
</body>
</html>
