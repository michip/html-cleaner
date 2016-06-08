<?php

interface AttributeCleaner
{
    public static function cleanAttribute($elementName, $attributeName, $attributeValue, $advancedOptions);
}

class DefaultAttributeCleaner implements AttributeCleaner
{

    public static function cleanAttribute($elementName, $attributeName, $attributeValue, $value)
    {
        if ($attributeValue == $value) {
            return $attributeValue;
        }

        return null;
    }
}

class ClassAttributeCleaner implements AttributeCleaner
{
    public static function cleanAttribute($elementName, $attributeName, $attributeValue, $allowedClasses)
    {
        $newClassAttributeValue = "";
        $currentClasses = explode(' ', $attributeValue);

        foreach($allowedClasses as $currentAllowedClass){
            if(in_array($currentAllowedClass, $currentClasses)){
                $newClassAttributeValue .= $currentAllowedClass." ";
            }
        }

        if(empty($newClassAttributeValue)) {
            return null;
        }

        $newClassAttributeValue = trim($newClassAttributeValue);

        return $newClassAttributeValue;
    }
}

class StyleAttributeCleaner implements AttributeCleaner
{

    public static function cleanAttribute($elementName, $attributeName, $attributeValue, $styleOptions)
    {
        $newStyleAttributeValue = "";
        $currentStyleAttributes = array();
        $tempStyleAttributes = explode(';', $attributeValue);

        foreach ($tempStyleAttributes as &$currentTempStyleAttribute) {
            $currentTempStyleAttribute = explode(':', $currentTempStyleAttribute);
            $currentTempStyleAttribute = array_map('trim', $currentTempStyleAttribute);

            if (!empty($currentTempStyleAttribute[0]) && !empty($currentTempStyleAttribute[1])) {
                $currentStyleAttributes[$currentTempStyleAttribute[0]] = $currentTempStyleAttribute[1];
            }
        }

        $newStyleAttributes = array();

        foreach ($styleOptions as $currentValidStyleAttribute => $currentValidStyleAttributeValue) {

            if (array_key_exists($currentValidStyleAttribute, $currentStyleAttributes)) {

                if ($currentValidStyleAttributeValue == null) {
                    $newStyleAttributes[$currentValidStyleAttribute] = $currentStyleAttributes[$currentValidStyleAttribute];
                    continue;
                }

                if ($currentStyleAttributes[$currentValidStyleAttribute] == $currentValidStyleAttributeValue) {
                    $newStyleAttributes[$currentValidStyleAttribute] = $currentValidStyleAttributeValue;
                    continue;
                }
            }
        }

        if (empty($newStyleAttributes)) {
            return null;
        }

        foreach ($newStyleAttributes as $currentStyleAttribute => $currentStyleAttributeValue) {
            $newStyleAttributeValue .= $currentStyleAttribute . ":" . $currentStyleAttributeValue . ";";
        }

        return $newStyleAttributeValue;
    }
}