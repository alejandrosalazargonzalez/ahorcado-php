<?php
/**
 * @author alejandrosalazargonzalez
 * @version 1.0.0
 * dibuja el ahorcado en ASCII
 */

namespace classes;
class Renderer {
    public function ascii(int $attemptsLeft): string {
        $stages = [
            0 => "
              _____
              |   |
              O   |
             /|\  |
             / \  |
                  |
            ______|
            ",
            1 => "
              _____
              |   |
              O   |
             /|\  |
             /    |
                  |
            ______|
            ",
            2 => "
              _____
              |   |
              O   |
             /|\  |
                  |
                  |
            ______|
            ",
            3 => "
              _____
              |   |
              O   |
             /|   |
                  |
                  |
            ______|
            ",
            4 => "
              _____
              |   |
              O   |
              |   |
                  |
                  |
            ______|
            ",
            5 => "
              _____
              |   |
              O   |
                  |
                  |
                  |
            ______|
            ",
            6 => "
              _____
              |   |
                  |
                  |
                  |
                  |
            ______|
            ",
        ];
        return "<pre>" . $stages[$attemptsLeft] . "</pre>";
    }
}