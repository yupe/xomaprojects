<?php
/**
 *  YandexJsApi class file
 *
 *  @author Opeykin A. <aopeykin@gmail.com>
 *  @link  http://allframeworks.ru/ 
 *  @copyright 2009-2010 allframeworks.ru
 *  @license BSD License
 *
 */

/**
 *
 * YandexJsApi provides helper methods to easily access {@link http://api.yandex.ru/jslibs/ Yandex JavaScript API (Yandex CDN)}.
 * @author Opeykin A. <aopeykin@gmail.com>
 * @version 0.0.1
 * @package helpers
 *
 */

class YandexJsApi
{
    const LOADER_URL = 'http://yandex.st/jslibs/loader.js';
    
    private static $loaded = false;
    
    private static $jsLibs = array('dojo' => array(
                                'versions'   => array('1.4.0','1.3.2','1.2.3'),
                                'staticPath' => 'http://yandex.st/dojo/{version}/dojo/dojo/dojo.xd.js',
                                'develPath'  => 'http://yandex.st/dojo/1.4.0/dojo/dojo.xd.js.uncompressed.js'
                            ),
                            'extcore' => array(
                                'versions'   => array('3.1.0','3.0.0'),
                                'staticPath' => 'http://yandex.st/ext-core/{version}/ext-core.min.js',
                                'develPath'  => 'http://yandex.st/ext-core/{version}/ext-core.js'
                            ),
                            'json2' => array(
                                'versions' => array('2009-09-29'),
                                'staticPath' => 'http://yandex.st/json2/{version}/json2.min.js',
                                'develPath'  => 'http://yandex.st/json2/{version}/json2.js'
                            ),
                            'jquery' => array(
                                'versions'   => array('1.4.2','1.4.1','1.4.0','1.3.2','1.3.1','1.3.0','1.2.6'),
                                'staticPath' => 'http://yandex.st/jquery/{version}/jquery.min.js',
                                'develPath'  => 'http://yandex.st/jquery/{version}/jquery.js'
                            ),
                            'jquery-ui' => array(
                                'versions'   => array('1.8.1','1.8.0','1.7.2','1.7.1','1.7.0','1.6.0'),
                                'staticPath' => 'http://yandex.st/jquery-ui/{version}/jquery-ui.min.js',
                                'develPath'  => 'http://yandex.st/jquery-ui/1.8.0/jquery-ui.js'
                            ),
                            'mochikit' => array(
                                'versions'   => array('1.4.2','1.4.1','1.4.0','1.3.1','1.3.0'),
                                'staticPath' => 'http://yandex.st/mochikit/{version}/mochikit.min.js',
                                'develPath'  => 'http://yandex.st/mochikit/{version}/mochikit.js'
                            ),
                            'mootools' => array(
                                'versions'   => array('1.2.4','1.2.3'),
                                'staticPath' => 'http://yandex.st/mootools/{version}/mootools.min.js',
                                'develPath'  => 'http://yandex.st/mootools/{version}/mootools.js'
                            ),
                            'prototype' => array(
                                'versions'   => array('1.6.1.0','1.6.0.3','1.6.0.2'),
                                'staticPath' => 'http://yandex.st/prototype/{version}/prototype.min.js',
                                'develPath'  => 'http://yandex.st/prototype/{version}/prototype.js'
                            ),
                            'pure' => array(
                                'versions'   => array('2.25','1.35'),
                                'staticPath' => 'http://yandex.st/pure/{version}/pure.min.js',
                                'develPath'  => 'http://yandex.st/pure/{version}/pure.js'
                            ),
                            'raphael' => array(
                                'versions'   => array('1.3.2','1.3.1','1.3.0','1.2.7','1.2.6','1.0'),
                                'staticPath' => 'http://yandex.st/raphael/{version}/raphael.min.js',
                                'develPath'  => 'http://yandex.st/raphael/{version}/raphael.js'
                            ),
                            'scriptaculous' => array(
                                'versions'   => array('1.8.3','1.8.2','1.8.1'),
                                'staticPath' => 'http://yandex.st/scriptaculous/{version}/min/scriptaculous.js',
                                'develPath'  => 'http://yandex.st/scriptaculous/{version}/scriptaculous.js'
                            ),
                            'swfobject' => array(
                                'versions'   => array('2.2','2.1'),
                                'staticPath' => 'http://yandex.st/swfobject/{version}/swfobject.min.js',
                                'develPath'  => 'http://yandex.st/swfobject/{version}/swfobject.js'
                            ),
                            'yui' => array(
                                'versions'   => array('3.1.0','3.0.0'),
                                'staticPath' => 'http://yandex.st/yui/{version}/yui/yui-min.js',
                                'develPath'  => 'http://yandex.st/yui/{version}/yui/yui.js'
                            )
                            
                            
                     );
    
    /**
	 * Renders the Yandex API file.	 
	 * @return string the script tag that loads http://yandex.st/jslibs/loader.js.
	 */
    
    public function init()
    {
        self::$loaded = true;
        return CHtml::scriptFile(self::LOADER_URL);
    }
    
    
    /**
	 * Check accessibility and needed version on Yandex CDN {@see http://api.yandex.ru/jslibs/doc/dg/reference/jslibs.xml}
     * @param string name of library or framework
     * @param string library or framework version
	 * @return boolean true - if library and version is supported by Yandex CDN	 
	 */
    
    public static function checkSupport($lib,$version)
    {
        $lib = strtolower(trim($lib));
        if(!array_key_exists($lib,self::$jsLibs))
        {
            return false;
        }        
        if(!in_array($version,self::$jsLibs[$lib]['versions']) && $version !== null)
        {
            return false;
        }        
        return true;
    }
    
    /**
	 * Get full static path for library (file) on Yandex CDN servers
     * @param string name of library or framework
     * @param string library or framework version
     * @param boolean return devel version of library
	 * @return string full library url
	 */
    
    public static function getLibPath($lib,$version,$devel=false)
    {
        if(self::checkSupport($lib,$version))
        {
            $libUrl = $devel ? self::$jsLibs[$lib]['develPath'] : self::$jsLibs[$lib]['staticPath'];
            $libUrl = str_replace('{version}',$version,$libUrl);
            return $libUrl;
        }
        else
        {
            return '';
        }
    }
    
    
    /**
	 * Register script file using Yii::app()->getClientScript()->registerScriptFile() method
     * @param string name of library or framework
     * @param string library or framework version
     * @param boolean return devel version of library
     * @param int position for script include @see http://www.yiiframework.com/doc/api/CClientScript#registerScriptFile-detail
	 * @return boolean true if file successfully registered
	 */
    public static function registerStaticFile($lib,$version,$devel=false,$position=CClientScript::POS_HEAD)
    {
        if(self::checkSupport($lib,$version))
        {
            $libUrl = self::getLibPath($lib,$version,$devel);
            if($libUrl)
            {
                Yii::app()->getClientScript()->registerScriptFile($libUrl,$position);
                return true;
            }           
        }        
        return false;        
    }
    
    /**
	 * Load library file using Ya.load() method
     * @param string name of library or framework
     * @param string library or framework version
     * @param array additional parametrs for Ya.load()
     *    metrikra - metrika ID (http://metrika.yandex.ru/)
     *    onload   - function executed after library is loaded
     *    uncompressed - get uncompressed version of file
     *    more details http://api.yandex.ru/jslibs/doc/dg/tasks/how-to-add-a-lib.xml
	 * @return string method call for load library
	 */
    
    public static function load($lib,$version=null,$parms=null)
    {        
        if(!self::$loaded)
        {
            throw new CHttpException('Please call YandexJsApi::init() before YandexJsApi::load()!');
        }
        
        if(!self::checkSupport($lib,$version))
        {
            throw new CHttpException("Library '$lib' version '$version' not supported by Yandex CDN! See http://api.yandex.ru/jslibs/doc/dg/reference/jslibs.xml!");
        }        
        $parmsString = '{}';
        if(is_array($parms) && count($parms))
        {
            if(isset($parms['onload']))
            {
                $parms['onload'] = 'js:'.$parms['onload'];
            }
            $parmsString = CJavaScript::encode($parms);            
        }
        
        $lib = strtolower(trim($lib));        
        return $version ? "Ya.load('$lib','$version',$parmsString);" : "Ya.load('$lib',null,$parmsString);";        
    }
    
    
    
}
?>

