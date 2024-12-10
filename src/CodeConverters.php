<?php
namespace htmlfilter;
require 'src/Source.php';
use DOMDocument;
use Exception;

/**
 *  Класс, содержащий функции перевода из/в HTML в/из DOMDocument
 */
class CodeConverters {

    private $htmlLoader;

    public function __construct() {
        $this->htmlLoader = new GetHtml();
    }

    /**
     * Постройка DOM-дерева из HTML из url/файла
     * @param string $source url/файл, содержащий HTML
     * @return DOMDocument построенное дерево
     */
    public function parse(string $source) {
        try {
            // Загружаем HTML
            $html = $this->htmlLoader->getContent($source);
            $domDocument = new DOMDocument();
            libxml_use_internal_errors(true); // Игнорируем ошибки парсинга
            $domDocument->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
            libxml_clear_errors();

            return $domDocument;
        } 
        catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
            return null;
        }
    }
    
    /**
     * Генерация HTML кода из DOM-дерева
     * @param DOMDocument $domDocument дерево, которое необходимо конвертировать в HTML
     * @return string полученная строка HTML
     */
    public function codeGenerator(DOMDocument $domDocument){
        $domDocument->encoding = 'UTF-8';
    
        // Получаем HTML
        $html = $domDocument->saveHTML();
    
        // Возвращаем HTML с правильной кодировкой
       return mb_convert_encoding($html, 'UTF-8', 'HTML-ENTITIES');

    }
}