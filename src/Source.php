<?php
namespace htmlfilter;

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
            $urlHtml = file_get_contents($source);
            if ($urlHtml === false) {
                echo ("Не удалось загрузить содержимое по URL: {$source}");
            }
            return $urlHtml;
        }
        elseif (file_exists($source)) {
            // Если это файл, загружаем содержимое из файла
            return file_get_contents($source);
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