<?php
require 'vendor/autoload.php';
use htmlfilter\CodeConverters;
use htmlfilter\Process;
use htmlfilter\Store;
use htmlfilter\RuleCleaningStylesFromElement;
use htmlfilter\RuleCombineCommonElements;
use htmlfilter\GetConfig;
require 'src/Process.php';
require 'src/Rules/IHtmlConverter.php';
require 'src/Rules/RuleCombineCommonElements.php';
require 'src/Rules/RuleCleaningStylesFromElement.php';


/**
 * Класс, делегирующий работой всей программы
 */
class Fomatter{
    /**
     * Функция запуска опрограммы. Осуществляет делегирование работой всей программы
     * @param string $sourceHtmlIn url/файл, из которого берётся HTML код для форматирования
     * @param string $sourceHtmlOut файл для сохранения отформатированного HTML кода
     */
    public function start(string $sourceHtmlIn, string $sourceHtmlOut){
        //передаем входной код html в метод для постройки дерева
        $converter = new CodeConverters();
        $domDocument = $converter->parse($sourceHtmlIn);
        if ($domDocument === false){
            return;
        }
        
        $config = new GetConfig();
        $rules = [
            new RuleCombineCommonElements($config->getContent(__DIR__ . '/Configs/configArrayRuleCombine.json')), 
            new RuleCleaningStylesFromElement($config->getContent(__DIR__ . "/Configs/configDictionaryRuleCleaning.json"))
        ];
        $process = new Process($rules);

        // передаём дерево на обход, где будут применяться правила
        $process->traverse($domDocument);
    
        //передаем дерево для обратного преобразования в код
        $outputHtml = $converter->codeGenerator($domDocument);
    
        //передаем путь файла в конструктор
        $store = new Store($sourceHtmlOut);
    
        //передаем код для сохранения в файл 
        $store->saveHtmlToFile($outputHtml);
    }
}

$sourceHtmlIn = false;
$sourceHtmlOut = false;

//получаем аргумент, в котором содержится путь/ссылка к файлу с html кодом
if (isset($argv[1])){
    $sourceHtmlIn = $argv[1];
}

//получаем аргумент, в котором содержиться путь к файлу, в котором будет сохранен преобразованный html код
if (isset($argv[2])){
    $sourceHtmlOut = $argv[2];
}

// проверяем корректность получения аргументов из командной строки
if ($sourceHtmlIn === false || $sourceHtmlOut === false){
    echo "Ошибка ввода. Недостаточно аргументов.";
    exit();
}
//запуск программы
$formatter = new Fomatter();
$formatter->start($sourceHtmlIn, $sourceHtmlOut);

//$source = 'https://mdou26.edu.yar.ru/foto.html?with_template=empty'; // файл или ссылка константа