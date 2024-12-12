<?php
namespace htmlfilter;

use Exception;

/**
 * Интерфейс для классов блока SOURCE
 */
interface IGetContentFromFile{
    function getContent(string $path);
}
/**
 * Класс, производящий получение HTML кода из url/файла
 */
class GetHtml implements IGetContentFromFile {
    /**
     * Получение HTML кода из url/файла
     * @param string $source url/файл, из которого необходимо получить HTML код
     * @return string полученный HTML код
     * @return false в случаях некорректного url/файла
     */
    public function getContent(string $source) {
        if (filter_var($source, FILTER_VALIDATE_URL)) {
            // Если это URL, загружаем содержимое по URL
            try{
                set_error_handler(function () {
                    throw new Exception();
                });                
                $urlHtml = file_get_contents($source);
                restore_error_handler();
            }
            catch (Exception){
                echo ("Не удалось загрузить содержимое по URL: {$source}");
                restore_error_handler();
                return false;
            }
            if ($urlHtml === false) {
                echo ("Не удалось загрузить содержимое по URL: {$source}");
                return false;
            }
            return $urlHtml;
        }
        elseif (file_exists($source)) {
            // Если это файл, загружаем содержимое из файла
            $fileHtml = file_get_contents($source);
            if ($fileHtml === false) {
                echo ("Не удалось загрузить содержимое файла: {$source}");
                return false;
            }
            return $fileHtml;
        } 
        echo ("Неверный источник: {$source}");
        return false;
    }

}

/**
 * Класс, производящий получение конфигурационных файлов для правил
 */
class GetConfig implements IGetContentFromFile{
    /**
     * Получение конфигурационного файла для правила
     * @param string $configPath путь до конфигурационного файла
     * @return array полученный словарь правил
     * @return false в случаях некорректного файла
     */
    public function getContent(string $configPath){
        // Читаем содержимое файла
        $jsonContent = file_get_contents($configPath);
        if ($jsonContent === false) {
            echo ("Could not open config file");
            return false;
        }

        // Декодируем JSON
        $elementStylesDictionary = json_decode($jsonContent, true);
        if ($elementStylesDictionary === null || json_last_error() !== JSON_ERROR_NONE) {
            echo ("Unserialization failed: " . json_last_error_msg());
            return false;
        }
        return $elementStylesDictionary;
    }
}