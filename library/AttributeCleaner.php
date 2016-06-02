<?php

interface AttributeCleaner{
    public static function cleanAttribute($elementName, $attributeName, $attributeValue, $advancedOptions);
}

class DefaultAttributeCleaner implements AttributeCleaner{

    public static function cleanAttribute($elementName, $attributeName, $attributeValue, $value)
    {
        if($attributeValue == $value){
            return $attributeValue;
        }

        return null;
    }
}

class StyleAttributeCleaner implements AttributeCleaner{

    public static function cleanAttribute($elementName, $attributeName, $attributeValue, $styleOptions)
    {
        $newStyleAttributeValue = "";
        $currentStyleAttributes = array();
        $tempStyleAttributes = explode(';', $attributeValue);

        foreach($tempStyleAttributes as &$currentTempStyleAttribute){
            $currentTempStyleAttribute = explode(':', $currentTempStyleAttribute);
            $currentTempStyleAttribute = array_map('trim', $currentTempStyleAttribute);

            if(!empty($currentTempStyleAttribute[0]) && !empty($currentTempStyleAttribute[1])){
                $currentStyleAttributes[$currentTempStyleAttribute[0]] = $currentTempStyleAttribute[1];
            }
        }

        $newStyleAttributes = array();

        foreach($styleOptions as $currentValidStyleAttribute => $currentValidStyleAttributeValue){
            if(array_key_exists($currentValidStyleAttribute, $currentStyleAttributes) && $currentStyleAttributes[$currentValidStyleAttribute] == $currentValidStyleAttributeValue){
                $newStyleAttributes[$currentValidStyleAttribute] = $currentValidStyleAttributeValue;
            }
        }

        if(empty($newStyleAttributes)){
            return null;
        }

        foreach ($newStyleAttributes as $currentStyleAttribute => $currentStyleAttributeValue){
            $newStyleAttributeValue .= $currentStyleAttribute.":".$currentStyleAttributeValue.";";
        }

        return $newStyleAttributeValue;
    }
}