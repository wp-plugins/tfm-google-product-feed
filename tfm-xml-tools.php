<?php

/**
* Made with ❤ by themesfor.me
*
* XML Tools
*/

class TFM_XML_TOOLS
{
  /**
   * Insert parameters into XML in form of {{ parameter }}. Parameters name should be $params array keys and value is arrays value.
   *
   * @param $xml Raw XML string with template markings
   * @param $params Parameters in format $params['param_name'] => $value;
   * @return string XML file ready for print
   */
  public static function render_xml($xml, $params)
  {
    $replacer = function ($match) use ($params)
    {
      return isset($params[$match[1]]) ? $params[$match[1]] : $match[0];
    };

    return preg_replace_callback('/{{\s*(.+?)\s*}}/', $replacer, $xml);
  }

  /**
   * Clean the XML from all empty nodes
   *
   * @param $xml Raw XML string
   * @return string XML without empty nodes
   */
  public static function remove_empty_nodes($xml)
  {
    $doc = new \DOMDocument();
    $doc->preserveWhiteSpace = false;
    $doc->loadXML($xml);

    $xpath = new \DOMXPath($doc);

    foreach($xpath->query('//*[not(node())]') as $node ) {
      $node->parentNode->removeChild($node);
    }

    $doc->formatOutput = true;
    return $doc->saveXML();
  }
}
