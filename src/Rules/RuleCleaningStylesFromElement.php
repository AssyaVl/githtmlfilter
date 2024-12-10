<?php
namespace htmlfilter;
use htmlfilter\IHtmlConverter;
use DOMElement;

/**
 * Класс правила очистки стилей
 */
class RuleCleaningStylesFromElement implements IHtmlConverter{
    private $configDictionaryRuleCleaning;

    public function __construct(array $config)
    {
        $this->configDictionaryRuleCleaning = $config !== false ? $config : null;
    }
    
    /**
     * Применение правила очистки стилей.
     * Функция производит проверку элемента на соответствие правилу и производит очистку стилей.
     * Необходимо наличие элемента в конфигурационном файле данного правила.
     * @param DOMElement $element Проверяемый элемент
     * @return DOMElement Проверенный на правило элемент
     */
    function convert(DOMElement $element){

        $tagName = $element->tagName;

        // Проверяем, есть ли в конфигурации разрешенные стили для данного тега
        if (isset($this->configDictionaryRuleCleaning[$tagName])) {
            $this->filterStyleAttribute($element, $this->configDictionaryRuleCleaning[$tagName]);
        }
        return $element;
    }

    private function filterStyleAttribute(\DOMElement &$element, array $allowedValues) {
        $elementStyle = $element->getAttribute('style'); // Получаем значение атрибута style
        if ($elementStyle) {
            $styles = $this->parserIntoArray($elementStyle); // Массив строк со стилями
            $newStyles = []; // Новый список для отфильтрованных стилей

            foreach ($styles as $style) {
                $style = trim($style);
                $parts = explode(':', $style, 2); // Разделяем стиль на имя и значение

                if (count($parts) >= 2) {
                    list($styleName, $styleValue) = $parts; // Разделяем стиль на 2 части, если он двойной. color:red
                } else {
                    $styleName = $style;
                    $styleValue = '';
                }

                $styleName = trim($styleName);

                // Если стиль разрешен, добавляем его в новый список стилей
                if (in_array($styleName, $allowedValues)) {
                    array_push($newStyles, $style);
                }
            }

            // Устанавливаем новый атрибут style, если остались разрешенные стили
            if (!empty($newStyles)) {
                $newStyleString = implode(';', $newStyles); // Объединяем все новые разрешенные стили из массива в строку
                $element->setAttribute('style', $newStyleString); // Устанавливаем новый атрибут, используя строку с разрешенными стилями
            } else {
                // Если нет разрешенных стилей, удаляем атрибут style
                $element->removeAttribute('style');
            }
        }
    }

    private function parserIntoArray($styleString) {
        $styles = explode(';', $styleString);//массив строк со стилями
        return $styles;
    }
}