<?php
namespace htmlfilter;
use htmlfilter\IHtmlConverter;
use DOMElement;

/**
 * Класс правила объединения общих элементов
 */
class RuleCombineCommonElements implements IHtmlConverter{
    private $configArrayRuleCombine;

    public function __construct(array $config)
    {
        $this->configArrayRuleCombine = $config !== false ? $config : null;
    }

    /**
     * Применение правила объединения общих элементов.
     * Функция производит проверку элемента на соответствие правилу и производит объединение общих элементов.
     * Необходимо наличие элемента в конфигурационном файле данного правила.
     * @param DOMElement $element Проверяемый элемент
     * @return DOMElement Проверенный на правило элемент
     */
    public function convert(DOMElement $element){
        // проверяем, находится ли элемент в конфиге
        if (in_array($element->nodeName, $this->configArrayRuleCombine['elements'])) {
            // проверяем, имеет ли элемент единственного ребёнка, совпадающего с ним самим
            // исключения составляют пробельные текстовые дети
            if ($this->hasOnlySameChild($element)){
                // берём ребёнка совпадающего с родителем
                $childElement = $element->getElementsByTagName($element->nodeName)->item(0);

                // сначала пройдёмся по его детям, чтобы объединить элементы вложенные в него самого
                $this->convert($childElement); 
        
                // передаём атрибуты и детей
                $this->combineAttributes($childElement);
                $this->combineChildren($childElement);
    
                // удаление ребенка
                $element->removeChild($childElement);
            }
        }
        return $element;
    }
    private function hasOnlySameChild(DOMElement $element){
        // случай когда дочерний элемент один и совпадает с родительским
        if ($element->childNodes->count() === 1){
            $childElement = $element->childNodes->item(0);
            // проверяем, совпадают ли названия элементов родителя и ребёнка
            if ($childElement instanceof DOMElement && $childElement->nodeName === $element->nodeName){
                return true;
            }
            return false;
        }
        // случай когда родитель имеет детей в виде пробельных символов и 1 такой же элемент как он сам
        // (по предположению, "пробельных" детей должно быть не больше двух - до ребёнка-элемента и после)
        else if ($element->childNodes->count() > 1 && $element->childNodes->count() <= 3){
            // пробегаем детей элемента и удаляем их в случае если это текст из пробельных символов
            $childCount = $element->childNodes->count();
            foreach ($element->childNodes as $childElement){
                if ($childElement->nodeType === XML_TEXT_NODE && trim($childElement->textContent) === ""){
                    $childCount--;
                }
            }
            // если остался 1 ребёнок такой же, как и родительский элемент, возвращаем true
            if ($childCount === 1){
                foreach ($element->childNodes as $childElement){
                    if ($childElement->nodeName === $element->nodeName){
                        return true;
                    }
                }
            }
        }
        return false;
    }
    private function combineAttributes(DOMElement &$childElement){ // передаём аттрибуты от ребёнка к родителю (если имеются)
        if ($childElement->hasAttributes()) {
            $parentElement = $childElement->parentNode;
            if ($parentElement instanceof DOMElement) {
                foreach ($childElement->attributes as $childAttribute) {
                    // если атрибут есть и у родителя и у ребёнка
                    if ($parentElement->hasAttribute($childAttribute->nodeName)) {
                        // получаем словари стилей родителя и ребёнка
                        $parentStylesDictionary = $this->getStylesDictionary($parentElement->getAttribute($childAttribute->nodeName));
                        $childStylesDictionary = $this->getStylesDictionary($childAttribute->nodeValue);

                        // проверяем на одинаковые стили атрибута, если такие есть, берём тот, который у ребёнка
                        $newStylesDictionary = [];
                        foreach ($parentStylesDictionary as $parentStyleName => $parentStyleValue){
                            $newStylesDictionary[$parentStyleName] = $parentStyleValue;
                        }
                        foreach ($childStylesDictionary as $childStyleName => $childStyleValue){
                            $newStylesDictionary[$childStyleName] = $childStyleValue;
                        }

                        // добавляем полученный атрибут к элементу
                        $newStylesArray = [];
                        foreach ($newStylesDictionary as $styleName => $styleValue){
                            $newStyle = $styleValue === "" ? $styleName : implode(':', array($styleName, $styleValue));
                            array_push($newStylesArray, $newStyle);
                        }
                        $newStyleString = implode(";", $newStylesArray);
                        $parentElement->setAttribute($childAttribute->nodeName, $newStyleString);
                    } 
                    // если атрибут есть у ребёнка, но нет у родителя
                    else if(!$parentElement->hasAttribute($childAttribute->nodeName)) {
                        $parentElement->setAttribute($childAttribute->nodeName, $childAttribute->nodeValue);
                    }
                }
            }
        }
    }
    private function combineChildren(DOMElement &$childElement){ // передаём детей ребёнка (внуков) к родителю (если имеются)
        if ($childElement->hasChildNodes()) {
            $parentElement = $childElement->parentNode;
            if ($parentElement instanceof DOMElement) {
                $textNodeMayBe = null;
                if ($parentElement->lastChild->nodeType === XML_TEXT_NODE) {
                    $textNodeMayBe = $parentElement->lastChild;
                }
                while ($childElement->firstChild) {
                    $parentElement->insertBefore($childElement->firstChild, $textNodeMayBe);
                }
            }
        }
    }
    private function getStylesDictionary(string $attributeValue){
        $attributeValue = trim($attributeValue, ";");
        $styles = explode(';', $attributeValue); //массив строк со стилями
        // получаем словарь стилей название => значение
        $stylesDictionary = [];
        foreach ($styles as $style) {
            $style = trim($style);
            $parts = explode(':', $style, 2);
            $stylesDictionary[$parts[0]] = isset($parts[1]) ? $parts[1] : "";
        }
        return $stylesDictionary;
    }
}