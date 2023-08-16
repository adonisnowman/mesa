<?php

use Phalcon\Crypt as Crypt;
use Phalcon\Security\JWT\Builder;
use Phalcon\Security\JWT\Signer\Hmac;
use Phalcon\Security\JWT\Token\Parser;
use Phalcon\Security\JWT\Validator;

class HtmlFiles
{
    public static function dom_dump($obj , $option = true , $convert = 'HTML-ENTITIES' ) {
        if ($classname = get_class($obj)) {

            $Return = "";
            $retval = "Instance of $classname, node list: \n";
            if(!empty($obj->attributes)) foreach ($obj->attributes as $attr)  echo "---[{$attr->nodeName}] = {$attr->nodeValue} --- ";
           
            switch (true) {
                case ($obj instanceof DOMDocument):
                    $Return = "XPath[DOMDocument]: {$obj->getNodePath()}\n".$obj->ownerDocument->saveHTML();
                    $Return = $obj->ownerDocument->saveHTML();
                    break;
                case ($obj instanceof DOMElement):
                    $Return = "XPath[DOMElement]: {$obj->getNodePath()}\n".$obj->ownerDocument->saveHTML();
                    $Return = $obj->ownerDocument->saveHTML();
                    break;
                case ($obj instanceof DOMAttr):
                    $Return = "XPath[DOMAttr]: {$obj->getNodePath()}\n".$obj->ownerDocument->saveHTML();
                    $Return = $obj->ownerDocument->saveHTML();
                    //$retval .= $obj->ownerDocument->saveXML($obj);
                    break;
                case ($obj instanceof DOMNodeList):
                    for ($i = 0; $i < $obj->length; $i++) {
                        $Return .= "Item #$i, XPath: {$obj->item($i)->getNodePath()}\n"."{$obj->item($i)->ownerDocument->saveHTML($obj->item($i))}\n";
                    }
                    break;
                default:
                    return "Instance of unknown class";
            }
        } else {
            return 'no elements...';
        }
        if($option) return $Return;
        return htmlspecialchars($retval.$Return);
    }
}

