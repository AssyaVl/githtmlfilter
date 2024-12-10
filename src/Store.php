<?php
namespace htmlfilter;

/**
 * Класс, содержащий метод сохранения строки HTML в файл
 */
class Store{
    private string $saveFilePath; // его берём из командной строки 

    public function __construct(string $saveFilePath){
        $this->saveFilePath = $saveFilePath;
    }

    /**
     * Сохранение HTML кода в файл
     * @param string $html строка, сохраняемая в файл
     */
    public function saveHtmlToFile(string $html){
        // Проверяем, существует ли файл
        if (file_exists($this->saveFilePath)) {
            // Открываем файл для записи
            $saveFile = fopen($this->saveFilePath, 'w');
            if (!$saveFile){
                echo ("Не удалось открыть файл для записи");
            }
            
            // Убедимся, что HTML-контент в UTF-8
            //$html = htmlspecialchars($html, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8');
            $html = mb_convert_encoding($html, 'UTF-8');

            // Записываем HTML-контент в файл
            fwrite($saveFile, $html);
    
            // Закрываем файл
            fclose($saveFile);

            return;
        } 
        echo("Файл не найден");
    }
}