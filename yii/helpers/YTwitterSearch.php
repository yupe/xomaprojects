<?php
/**
 *  YTwitterSearch
 *
 *  Класс хелпер для простого поиска в Twitter
 *
 *  @version 0.0.1
 *  @author Opeykin A. <aopeykin@gmail.com>
 *  @link http://allframeworks.ru
 *  @package helpers
 *
 *  @example $tweets = YTwitterSearch::getInstance('json',array('lang' => 'ru'))->search('Yii Framework');
 *
 */
class YTwitterSearch extends CComponent
{
    
    private static $instance;
    
    private $url;
    
    private $validParams;
    
    private $instanceParams;
    
    private $validFormats;
    
    private $instanceFormat;
    
    private function __construct()
    {
        $this->url = 'http://search.twitter.com/search.json';        
        $this->validParams   = array('callback','lang','locale','rpp','page','since_id','geocode','show_user');
        $this->validFormats  = array('atom','json');
        $this->instanceParams = array();
    }
    
    public static function getInstance($format = 'json',$params = '')
    {
        if(!is_object(self::$instance))
        {
            self::$instance = new YTwitterSearch();            
            
            if($format != 'json' && in_array($format,self::$instance->validFormats))
            {
                self::$instance->instanceFormat = $format;
                self::$instance->url = 'http://search.twitter.com/search.'.self::$instance->instanceFormat;
            }     
            
            if(is_array($params) && count($params))
            {                
                foreach($params as $key => $value)
                {
                    if(in_array(mb_strtolower($key),self::$instance->validParams))
                    {                      
                      self::$instance->instanceParams[] = urlencode($key).'='.urlencode($value);
                    }
                }               
            }
        }
        
        return self::$instance;
    }
    
    public function search($query)
    {
        if($query)
        {           
            if(count(self::$instance->instanceParams))
            {
                $url = implode('&',self::$instance->instanceParams);                
            }         
            $url  = self::$instance->url.'?'.$url.'&q='.urlencode($query);
            $curl = curl_init();            
            curl_setopt($curl,CURLOPT_URL,$url);
            curl_setopt($curl,CURLOPT_RETURNTRANSFER,TRUE);
            $result = curl_exec($curl);
            curl_close($curl);
            
            return $result;
        }        
        return false;        
    }
}

?>