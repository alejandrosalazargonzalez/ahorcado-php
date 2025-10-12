<?php
declare(strict_types=1);

namespace App\Presentation\Controllers;

use App\Domain\Entity\Game;
use App\Infraestructure\Persistence\JsonWordRepository;
use App\Infraestructure\Session\SessionStorage;
use App\Presentation\Views\Renderer;

final class GameController
{
    private array $config;
    private SessionStorage $storage;
    private Renderer $renderer;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->storage = new SessionStorage();
        $this->renderer = new Renderer();

        session_start();
    }
    
    public function handle(): void
    {
        if (isset($_POST['reset'])) {
            $this->storage->reset();
            header("Location: index.php");
            exit;
        }
        $state = $this->storage->get('state');
        if ($state) {
            $game = new Game($state['palabra'], $state['intentosMaximos'], $state);
        } else {
            $repo = new JsonWordRepository($this->config['storage']['words_file']);
            $word = $repo->randomWord();
            $game = new Game($word, $this->config['game']['max_attempts']);
        }

        if (isset($_POST['letter'])) {
            $letter = $_POST['letter'];
            $game->guess($letter);
        }

        $this->storage->set('state', $game->toState());

        $this->renderView($game);
    }

    private function renderView(Game $game): void
    {
        echo "<!DOCTYPE html>
    <html lang='es'>
    <head>
    <meta charset='UTF-8'>
    <title>Juego del Ahorcado</title>
    <link rel='stylesheet' href='style.css'>
    </head>
    <body>
    <h1>Juego del Ahorcado</h1>
    <p>" . implode(' ', str_split($game->getMaskedWord())) . "</p>
    <p>Intentos restantes: {$game->getAttemptsLeft()}</p>
    <p>Letras usadas: " . implode(', ', $game->getUsedLetters()) . "</p>
    {$this->renderer->ascii($game->getAttemptsLeft())}
    ";

        if ($game->isWon()) {
            echo "<h2>¡Ganaste!</h2>
                <form method='post'><button type='submit' name='reset'>Jugar de nuevo</button></form>";
        } elseif ($game->isLost()) {
            echo "<h2>Perdiste. La palabra era: {$game->toState()['palabra']}</h2>
                <form method='post'><button type='submit' name='reset'>Jugar de nuevo</button></form>";
        } else {
            echo "<form method='post'>
                <label for='letter'>Introduce una letra:</label>
                <input type='text' name='letter' id='letter' maxlength='1' required>
                <button type='submit'>Probar</button>
            </form>
            <form method='post'>
                <button type='submit' name='reset'>Reiniciar juego</button>
            </form>";
        }

        echo "</body></html>";
    }
}
