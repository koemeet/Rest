<?php
/**
 * Created by JetBrains PhpStorm.
 * User: steffenbrem
 * Date: 8/1/13
 * Time: 2:11 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Rest\Utils;

class Convert
{
    /**
     * @param $xml
     * @return array
     */
    public static function xmlToArray($xml)
    {
        $result = array();

        if ($xml->hasChildNodes())
        {
            $child  = $xml->childNodes->item(0);

            if ($child->attributes->length > 0)
            {
                $result[$child->attributes->item(0)->name] = $child->attributes->item(0)->value;
            }

            foreach ($child->childNodes as $element)
            {
                if ($element instanceof \DOMElement)
                {
                    $result[$child->nodeName][] = self::xmlElementsToArray($element);
                }
                else
                {
                    $result[$child->nodeName] = array();
                }
            }
        }

        return $result;
    }

    /**
     * @param \DOMElement $element
     * @return array|string
     */
    public static function xmlElementsToArray($element)
    {
        $array = array();

        if ($element->hasChildNodes())
        {
            $children = $element->childNodes;

            if ($children->length == 1)
            {
                $child = $children->item(0);
                return $child->nodeValue;
            }

            foreach ($children as $child)
            {
                $array[$child->nodeName] = self::xmlElementsToArray($child);
            }
        }
        else
        {
            return $element->nodeValue;
        }

        return $array;
    }
}