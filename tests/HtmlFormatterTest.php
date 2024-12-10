<?php
namespace tests;
require 'vendor/autoload.php';
namespace htmlfilter;
use PHPUnit\Framework\TestCase;
use htmlfilter\CodeConverters;
use htmlfilter\Process;
use htmlfilter\RuleCleaningStylesFromElement;
use htmlfilter\RuleCombineCommonElements;
use htmlfilter\GetConfig;
require 'src/Process.php';
require 'src/Rules/IHtmlConverter.php'; //возможно тут придерется из за повторного объвления интефейса
require 'src/Rules/RuleCombineCommonElements.php';
require 'src/Rules/RuleCleaningStylesFromElement.php';


class HtmlFormatterTest extends TestCase {

    /**
     * @dataProvider htmlConversionProvider
     */
    public function testFullHtmlConversion($inputHtmlPath, array $configPaths, $expectedHtmlPath) {
        // echo "///////////////////////////" . "\n";
        // echo $inputHtmlPath . "\n";
        // print_r($configPaths . "\n");
        // echo $expectedHtmlPath . "\n";
        // echo "/////////////////////////" . "\n";

        // Инициализация конвертера
        $converter = new CodeConverters();
        $domDocument = $converter->parse($inputHtmlPath);

        // Инициализация конфигурации и правил
        $config = new GetConfig();
        $rules = [];

        $configContent = $config->getContent($configPaths[0]);
        $rules[] = new RuleCombineCommonElements($configContent);

        $configContent = $config->getContent($configPaths[1]);
        $rules[] = new RuleCleaningStylesFromElement($configContent);

        // Инициализация процесса
        $process = new Process($rules);
        $process->traverse($domDocument);

        // Преобразование дерева обратно в HTML
        $outputHtml = $converter->codeGenerator($domDocument);
        $outputHtml = str_replace(["\r\n","\n","\r"],"", $outputHtml);



        // Загрузка ожидаемого результата
        $expectedHtml = file_get_contents($expectedHtmlPath);
        $expectedHtml = str_replace(["\r\n","\n","\r"],"", $expectedHtml);


        $this->assertEquals($expectedHtml, $outputHtml);
    }
    public static function htmlConversionProvider()
    {
        $baseDir = dirname(__DIR__);
        //создаем массив с конфиг файлами 
        $configPaths = [];
        for ($i = 1; $i <= 5; $i++)
        {
            $configPaths[] = [
                $baseDir . "/Configs/TestConfigs/configArrayRuleCombineTest" . $i . ".json",
                $baseDir . "/Configs/TestConfigs/configDictionaryRuleCleaningTest" . $i . ".json"
            ];
        }
        //массивы с входными файлами и файлами с ожидаемыми результатами
        $inputHtmlPaths = [];
        $expectedHtmlPaths = [];

        for ($i = 1; $i <= 5; $i++) {
            $inputHtmlPaths[] = $baseDir . "/InputHtml/InputHtml" . $i . ".txt";
            $expectedHtmlPaths[] = $baseDir . "/ExpectedResults/ExpectedResult" . $i . ".txt";
        }
        //формируем набор из входного файла, пары конфигов и файла с ожидаемыи результатом
        $data = [];
        foreach ($inputHtmlPaths as $index => $inputHtmlPath) {
            $data[] = [$inputHtmlPath, $configPaths[$index], $expectedHtmlPaths[$index]];
        }
        return $data;
    }
}
