<?php

/**
 * (c) Michael Perk
 *
 * A class which provides custom html code cleanup
 */
class HTMLCleaner
{
    /**
     * Keys: Valid tags which will be ignored while cleaning up.
     * Values (optional): Array of attributes and their values which shall not get removed by the cleaner.
     * For example ['id' => 'test'] option will cause that no id attribute with the value 'test' will get deleted.
     * In case you only want to keep certain styles you can provide an array of css rules to keep.
     * @var array
     */
    private $validTags = [
        'strike' => null,
        'i' => null,
        'u' => null,
        'strong' => null,
        'div' => null,
        'br' => null,
        'ul' => null,
        'li' => null,
        'ol' => null,
        'span' => [
            'style' => ['line-height' => 1.42857]
        ]
    ];

    /**
     * Invalid tags which should get removed including their content.
     * @var array
     */
    private $invalidTagsToDelete = ['script'];

    /**
     * The clean method will return a cleaned up version of the given html. If this function returns null, the given html code is broken or empty.
     * @param $html
     * @return null|string
     */
    public function clean($html){

        $html = $this->removeBannedElements($html);
        $dom = $this->generateDom($html);

        if(!$dom instanceof DOMDocument){
            return null;
        }

        $elements = $this->extractElementsFromDom($dom);

        if($elements == null){
            return null;
        }

        foreach ($elements as $element) {
            $this->cleanAttributesOfElement($element);
        }

        $correctHTMLBody = $dom->saveHTML();
        $correctHTMLBody = strip_tags($correctHTMLBody, $this->getAllowedTags());

        return $correctHTMLBody;
    }

    /**
     * The generateDom method will generate a DOMDocument object (without doctype, html and head) from a given html string.
     * @param $inputHtml
     * @return DOMDocument|null
     */
    private function generateDom($inputHtml){
        libxml_use_internal_errors(true); //prevent warning messages from displaying because of the bad HTML

        $dom = new DOMDocument();

        if(!$dom->loadHTML(mb_convert_encoding($inputHtml, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD)){
            return null;
        }

        return $dom;
    }

    /**
     * The extractElementsFromDom method extracts every element of a given DOMDocument into an array.
     * @param $dom
     * @return DOMNodeList|null
     */
    private function extractElementsFromDom($dom){
        $xpath = new DOMXPath($dom);

        if (false === ($elements = $xpath->query("//*"))){
            return null;
        }

        return $elements;
    }

    /** 
     * The removeBannedElements function will remove all unnecessary elements from a given html string.
     * @param $htmlInput
     * @return string
     */
    private function removeBannedElements($htmlInput){

        foreach($this->invalidTagsToDelete as $currentTag){
            $htmlInput = preg_replace('/<'.$currentTag.'\b[^>]*>(.*?)<\/'.$currentTag.'>/i', '', $htmlInput);
        }

        $cleanedHtml = strip_tags($htmlInput, $this->getAllowedTags());

        return $cleanedHtml;
    }

    /**
     * Given an element the cleanAttributesOfElement method will clear or reduce all attributes of the element according to the options defined in validTags
     * @param $element
     */
    private function cleanAttributesOfElement($element){

        $attributesToRemove = array();
        $elementName = $element->nodeName;

        foreach($element->attributes as $attributeName => $attributeNode) {

            $attributeValue = $attributeNode->nodeValue;

            if(isset($this->validTags[$elementName][$attributeName])){

                if($attributeName == 'style') {

                    $styleAttributes = array();

                    $styleAttributesTemp = explode(';', $attributeValue);
                    foreach ($styleAttributesTemp as &$styleAttribute){
                        $styleAttribute = explode(':', $styleAttribute);
                        $styleAttribute = array_map('trim', $styleAttribute);

                        if(!empty($styleAttribute[0]) && !empty($styleAttribute[1])) {

                            $styleAttributes[$styleAttribute[0]] = $styleAttribute[1];
                        }
                    }

                    $newStyleAttributes = array();

                    foreach ($this->validTags[$elementName][$attributeName] as $validStyleAttribute => $validStyleAttributeValue){
                        if(array_key_exists($validStyleAttribute, $styleAttributes)){
                            if($styleAttributes[$validStyleAttribute] == $validStyleAttributeValue){
                                $newStyleAttributes[$validStyleAttribute] = $validStyleAttributeValue;
                            }
                        }
                    }

                    $newAttributeValue = "";

                    if(empty($newStyleAttributes)){
                        $attributesToRemove[] = $attributeName;
                        continue;
                    }

                    foreach ($newStyleAttributes as $currentStyledAttribute => $currentStyledAttributeValue){
                        $newAttributeValue .= $currentStyledAttribute.":".$currentStyledAttributeValue.";";
                    }

                    $element->setAttribute($attributeName, $newAttributeValue);

                    continue;
                }

                if($attributeValue == $this->validTags[$elementName][$attributeName]){
                    continue;
                }
            }

            $attributesToRemove[] = $attributeName;
        }

        foreach($attributesToRemove as $currentName){
            $element->removeAttribute($currentName);
        }
    }

    /**
     * This method extracts all allowed tags to a string which you can use in the strip_tags function.
     * @return string
     */
    private function getAllowedTags(){

        $allowedTags = "";

        foreach($this->validTags as $allowedTag => $additionalOptions){
            $allowedTags .= "<".$allowedTag.">";
        }

        return $allowedTags;
    }

}