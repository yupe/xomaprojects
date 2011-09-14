<?php
/**
 *   YMarkItUp class file.
 *
 *   Простой виджет для подключения редактора http://markitup.jaysalvat.com/home/
 *
 *   @package widgets
 *   @author Opeykin A. <aopeykin@gmail.com>
 *   @version 0.0.1
 *   @link http://allframeworks.ru
 *   @example $this->widget('YMarkItUp',array('domId'=>'Page_body'));
 *   
 */
class YMarkItUp extends CWidget
{
    
    /**
     *  domId - id элемента (textarea), в котором должен отобразиться редактор
     *  @var string     
     */
    public $domId = null;
    
    /**
     *  string settingsJsFile - js-файл с настройками редактора
     *  Файл должен находтся в каталоге доступном AssetsManager
     *  @var string
     */
    public $settingsJsFile  = '/markitup/sets/default/set.js';
    
    /**
     *  @param string settingsCssFile - css-файл для "темизации" редактора
     *  Файл должен находтся в каталоге доступном AssetsManager
     *  @var string
     */ 
    public $settingsCssFile = '/markitup/sets/default/style.css';
    
    
    /**
     * settingScriptId - идентификатор скрипта на страничке
     * @var string
     */ 
    public $settingScriptId = 'YMarkItUpEditor';
    
    public function init()
    {
	if(!$this->domId || !$this->settingsJsFile || !$this->settingsCssFile || !$this->settingScriptId)
	{
	    throw new CHttpException('500','Please set domId, settingsFile, settingScriptId and settingsCssFile properties for YMarkItUp!');
	}        
        $baseUrl   = Yii::app()->getAssetManager()->publish(dirname(__FILE__).DIRECTORY_SEPARATOR.'web/');
        Yii::app()->getClientScript()->registerCoreScript('jquery');
        Yii::app()->getClientScript()->registerScriptFile($baseUrl.'/markitup/jquery.markitup.pack');
        Yii::app()->getClientScript()->registerCssFile($baseUrl.'/markitup/skins/markitup/style.css');
        Yii::app()->getClientScript()->registerCssFile($baseUrl.$this->settingsCssFile);       
        Yii::app()->getClientScript()->registerScriptFile($baseUrl.$this->settingsJsFile);
        Yii::app()->getClientScript()->registerScript($this->settingScriptId,'$(document).ready(function() {
                                                                $("#'.$this->domId.'").markItUp(mySettings);
                                                             });'
                                                     );	
		
    }
}

?>