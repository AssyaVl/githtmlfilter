<?php
namespace htmlfilter;
use DOMElement;

/**
 * Интерфейс для реализации работы правил по форматированию HTML страниц
 */
interface IHtmlConverter{
    function convert(DOMElement $element);
}