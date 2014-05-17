<?php namespace Maatwebsite\Excel\Parsers;

use \URL;

/**
 *
 * LaravelExcel CSS Parser
 *
 * @category   Laravel Excel
 * @version    1.0.0
 * @package    maatwebsite/excel
 * @copyright  Copyright (c) 2013 - 2014 Maatwebsite (http://www.maatwebsite.nl)
 * @author     Maatwebsite <info@maatwebsite.nl>
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt    LGPL
 */
class CssParser {

    /**
     * Parsed results
     * @var array
     */
    protected $results = array();

    /**
     * Preg match string
     * @var string
     */
    protected $matcher = '/(.+?)\s?\{\s?(.+?)\s?\}/';

    /**
     * Document DOM
     * @var [type]
     */
    public $dom;

    /**
     * DOM xml
     * @var [type]
     */
    protected $xml;

    /**
     * Style sheet links
     * @var array
     */
    protected $links = array();

    /**
     * Url scheme
     * @var [type]
     */
    protected $scheme;

    /**
     * Construct the view parser
     * @param HTML_Reader $reader [description]
     */
    public function __construct($dom)
    {
        $this->dom = $dom;
        $this->findStyleSheets()->parse();
    }

    /**
     * Lookup the class or id
     * @param  [type] $type [description]
     * @param  [type] $name [description]
     * @return [type]       [description]
     */
    public function lookup($type, $name)
    {
        switch($type)
        {
            case 'id':
                $name = '#' . $name;
                break;

            case 'class':
                $name = '.' . $name;
                break;
        }

        // Get the css
        $results = $this->toArray();

        // Return the css if known
        if(isset($results[$name]))
            return $results[$name];

        return array();
    }

    /**
     * Return array with CSS attributes
     * @return [type] [description]
     */
    public function toArray()
    {
        return $this->results;
    }

    /**
     * Find the stylesheets inside the view
     * @return [type] [description]
     */
    protected function findStyleSheets()
    {
        // Import the dom
        $this->importDom();

        // Get all stylesheet tags
        $tags = $this->getStyleSheetTags();

        foreach($tags as $node)
        {
            $this->links[] = $this->getCleanStyleSheetLink($node);
        }

        // We don't need duplicate css files
        $this->links = array_unique($this->links);
        return $this;

    }

    /**
     * Parse the links to css
     * @return [type] [description]
     */
    protected function parse()
    {
        foreach($this->links as $link)
        {
            $css =  $this->getCssFromLink($link);
            $this->breakCSSToPHP($css);
        }
    }

    /**
     * Break CSS into a PHP array
     * @param  [type] $css [description]
     * @return [type]      [description]
     */
    protected function breakCSSToPHP($css)
    {
        $results = array();

        preg_match_all($this->matcher, $css, $matches);

        foreach($matches[0] as $i => $original)
        {
            if(!starts_with($original, '@')) // ignore attributes starting with @ (like @import)
                $this->breakIntoAttributes($i, $matches);
        }
    }

    /**
     * Break css into attributes
     * @return [type] [description]
     */
    protected function breakIntoAttributes($i, $matches)
    {
        // Seperate attributes
        $attributes = explode(';', $matches[2][$i]);

        foreach($attributes as $attribute)
        {
            $this->breakIntoProperties($attribute, $i, $matches);
        }

    }

    /**
     * Break into css properties
     * @return [type] [description]
     */
    protected function breakIntoProperties($attribute, $i, $matches)
    {
        if (strlen(trim($attribute)) > 0 ) // for missing semicolon on last element, which is legal
        {
            // List properties with name and value
            list($name, $value) = explode(':', $attribute);
            $this->results[$matches[1][$i]][trim($name)] = $this->cleanValue($value);
        }
    }

    /**
     * Return a clean value
     * @param  [type] $value [description]
     * @return [type]        [description]
     */
    protected function cleanValue($value)
    {
        $value = trim($value);
        $value = str_replace('!important', '', $value);
        return trim($value);
    }

    /**
     * Import the dom
     * @return [type] [description]
     */
    protected function importDom()
    {
        return $this->xml =  simplexml_import_dom($this->dom);
    }

    /**
     * Get all stylesheet tags
     * @return [type] [description]
     */
    protected function getStyleSheetTags()
    {
        return $this->xml->xpath('//link[@rel="stylesheet"]');
    }

    /**
     * Get the clean link to the stylesheet
     * @param  [type] $node [description]
     * @return [type]       [description]
     */
    protected function getCleanStyleSheetLink($node)
    {
        // Get the link
        $link = $node->attributes()->href;

        if (substr($link, 0, 4) != 'http')
        {
            if (substr($link, 0, 1) == '/') {
                $link = $this->getUrlScheme() . '://' . $link;
            } else {
                $link = $this->getUrlScheme() . '://' . dirname($this->getUrlScheme()) . '/' . $link;
            }
        }

        return $link;
    }

    /**
     * Get the URL scheme
     * @return [type] [description]
     */
    protected function getUrlScheme()
    {
        if(!$this->scheme)
            $this->scheme = parse_url(URL::getScheme());

        return $this->scheme;
    }

    /**
     * Get css from link
     * @param  [type] $link [description]
     * @return [type]       [description]
     */
    protected function getCssFromLink($link)
    {
        return file_get_contents($link);
    }

}