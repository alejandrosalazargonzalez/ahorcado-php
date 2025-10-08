<?php
/**
 * @author alejandrosalazargonzalez
 * @version 1.0.0
 */
namespace classes;
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
