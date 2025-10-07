<?php
$config = require __DIR__ . '/../config/config.php';

$wordsPath   = $config['storage']['words_file'];
$gamesPath   = $config['storage']['games_file'];
$maxAttempts = (int)$config['game']['max_attempts'];
declare(strict_types=1);

use App\Presentation\Controllers\GameController;

require __DIR__ . '/../src/Infrastructure/Autoload/Autoloader.php';
\App\Infrastructure\Autoload\Autoloader::register('App\'', __DIR__ . '/../src');

$config = require __DIR__ . '/../config/config.php';
//$controller = new GameController($config);
$controller->handle();

include './classes/SessionStorage.php';
include './classes/Game.php';
include './classes/Renderer.php';
include './classes/WordProvider.php';


use classes\Game as Game ;
use classes\Renderer as Renderer;
use classes\WordProvider as WordProvider;
use classes\SessionStorage as SessionStorage;
session_start();

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
    $wordProvider = new WordProvider("./data/palabras.txt");
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
    <p><?php echo implode(" ", str_split($game->getMaskedWord())); ?></p>
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
