<?php
namespace htmlfilter;
// require 'src/Rules/IHtmlConverter.php';
// require 'src/Rules/RuleCombineCommonElements.php';
// require 'src/Rules/RuleCleaningStylesFromElement.php';

use DOMDocument;
use DOMElement;

/**
 * Класс обхода по дереву HTML, запускающий проверку кода на соответствие правилам
 */
class Process{
    private $rules;

    public function __construct(array $rulesArray)
    {
        $this->rules = $rulesArray;
    }

    /**
     * Обход по дереву HTML и проверка на соответствие правилам форматирования
     * @param DOMDocument $domDocument дерево, отправляемое на обход
     */
    public function traverse(DOMDocument &$domDocument){

        if ($domDocument->hasChildNodes()) {
            $childCount = $domDocument->childNodes->length; // Используем length вместо childElementCount
    
            for ($i = 0; $i < $childCount; $i++) {
                $domChild = $domDocument->childNodes->item($i); // берем ребенка
    
                if ($domChild->nodeType === XML_ELEMENT_NODE) { // проверяем, является ли узел элементом
                    $this->traverseDomChild($domChild); // отправляем во внутреннюю рекурсивную функцию обхода
                }
            }
        }
    }

    /**
     * Рекурсивная функция обхода по элементам дерева и проверка на соответствие правилам форматирования
     * @param DOMElement $element элемент, отправляемый на обход
     */
    private function traverseDomChild(DOMElement &$element){
        // сразу отправляем элемент на все проверки
        foreach ($this->rules as $rule){
            $element = $rule->convert($element);
        }

        // проверяем, есть ли дети, которых нужно отправить на обход
        if ($element->hasChildNodes()) {
            $childCount = $element->childNodes->length; // Используем length вместо childElementCount

            for ($i = 0; $i < $childCount; $i++) {
                $childElement = $element->childNodes->item($i); // берем ребенка
                if ($childElement->nodeType === XML_ELEMENT_NODE) { // проверяем, является ли узел элементом
                    $this->traverseDomChild($childElement); // отправляем во внутреннюю рекурсивную функцию обхода
                }
            }
        }
    }
}