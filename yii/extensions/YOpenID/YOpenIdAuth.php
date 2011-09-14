<?php

/**
 *
 * Авторизация по OpenId для Yii фреймворка
 *
 * @package components
 * @author  xoma <aopeykin@gmail.com> <http://allframeworks.ru>
 * @version 0.0.1
 *
 *
 * Класс-врапер для http://openidenabled.com/php-openid/
 *
 * Пример использования
 *
 *
 * $auth = new YOpenIdAuth('http://localhost/user/openidfinal','http://localhost'); 
 *
 * if(isset($_GET['openid_identifier']))
 * {   
 *   try
 *   {
 *        $auth->authenticate($_GET['openid_identifier']);            
 *   }
 *   catch(Exception $e)
 *   {
 *        Yii::app()->user->setFlash('error',$e->getMessage()); 
 *   }
 *         
 * }
 *
 *
 * Завершение авторизации (код экшена, куда указывает returnTo)
 *
 * // получим массив данных о пользователе
 * $openIdUserData = $auth->finalAuth();  
 *
 *
 * 
 */


// библиотека http://openidenabled.com/php-openid/ должна располагаться в application.extensions.OpenId
Yii::import('application.extensions.OpenId.*');

require_once "Auth/OpenID/Consumer.php";
require_once "Auth/OpenID/FileStore.php";
require_once "Auth/OpenID/SReg.php";
require_once "Auth/OpenID/PAPE.php";


class YOpenIdAuth extends CComponent
{
    
    // ссылка на которую произойдет редирект после авторизации на сайте OpeId - провайдера
    protected $returnTo;
    
    // ссылка которая будет отображена на сайте OpenId - провайдера
    protected $trustRoot;
    
    
   /**
    *
    *   Коструктор
    *
    *   @param string returnTo - ссылка на которую произойдет редирект после авторизации на сайте OpeId - провайдера
    *
    *   @param string trustRoot - ссылка которая будет отображена на сайте OpenId - провайдера 
    *    
    */
    
    public function __construct($returnTo,$trustRoot)
    {	
           $this->returnTo  = $returnTo;
           $this->trustRoot = $trustRoot;
	   
           define('Auth_OpenID_RAND_SOURCE', null);
           
           global $pape_policy_uris;
           
           $pape_policy_uris = array( 
	                     	  PAPE_AUTH_MULTI_FACTOR_PHYSICAL,
			                  PAPE_AUTH_MULTI_FACTOR,
			                  PAPE_AUTH_PHISHING_RESISTANT
	                       );
	   
    }
    
    
    /**
    *
    *   getStorage()
    *
    *   @param string dir - каталог для хранения временных файлов (можно хранить в БД)
    *    
    *    
    */
    
    
    public function getStorage($dir = 'application.runtime.openid')
    {
         $path  = $dir;
         
         $rPath = YiiBase::getPathOfAlias($path);                 
                           
         if(!is_dir($rPath) || !is_writable($rPath))
         {
            throw new Exception('Storage dir is not writible or is does not exisit!');   
         }
         
         return new Auth_OpenID_FileStore($rPath);
    }
    
    
    public function getConsumer()
    {
          return new Auth_OpenID_Consumer($this->getStorage());
    }
    
    
    
    function getScheme()
    {
        $scheme = 'http';
        
        if (isset($_SERVER['HTTPS']) and $_SERVER['HTTPS'] == 'on')
        {
            $scheme .= 's';
        }
        
        return $scheme;
    }
    
    
    public function getReturnTo()
    {
        return $this->returnTo;
    }
    
    public function getTrustRoot()
    {
        return $this->trustRoot;
    }
    
    
  /**
    *
    *   authenticate - авторизовать пользователя
    *
    *   @param string openIdUrl - Url OpenId - провайдера
    *    
    *    
    */
    
    public function authenticate($openIdUrl)
    {
        if($openIdUrl)
        {
            
            // необходимо инициализировать сессию пользователя
            Yii::app()->user->setState('openIdUrl',$openIdUrl);
            
            $consumer = $this->getConsumer();
            
            $authRequest = $consumer->begin($openIdUrl);
            
            
            // указан Url не являющийся OpenId
            if(!$authRequest)
            {
                throw new Exception('Ошибка авторизации! Неправильный OpenId...');
            }
            
            
            $sregRequest = Auth_OpenID_SRegRequest::build(array('nickname'),array('fullname', 'email'));
            
            if($sregRequest)
            {
                $authRequest->addExtension($sregRequest);
            }
            
            
            if($authRequest->shouldSendRedirect())
            {
                 $redirectUrl = $authRequest->redirectURL($this->getTrustRoot(),$this->getReturnTo());
                 
                 if (Auth_OpenID::isFailure($redirectUrl))
                 {
                     throw new Exception('Cannot redirect to '.$redirectUrl->message);
                 }
                 else
                 {
                    header("Location:$redirectUrl");                    
                 }
            }
            else
            {
                
                $formId   = 'openid_message';
                
                $formHtml = $authRequest->formMarkup($this->getTrustRoot(),$this->getReturnTo(),false,array('id' => $formId));
                
                // Сделаем форму невидимой
                $s = " style=\"display:none\" ";
                
                $formHtml = str_replace("<form","<form $s",$formHtml);
                
                #$formHtml = '<b>redirection, please, wait...</b>'.$formHtml;

                
                if(Auth_OpenID::isFailure($form))
                {
                    throw new CHttpException('Cannot redirect to server!');
                }
                else
                {
                    print "<html>
                             <body  onload='document.getElementById(\"".$formId."\").submit()'>
                             $formHtml
                             </body>
                          </html>";
                   die();
                }               
              
            }
            
        }
        else
        {
            throw new Exception('OpenIdUrl is empty!');
        }
    }
    
    
    
  /**
    *
    *   finalAuth - завершение авторизации, получение данных от OpenId-провайдера
    *
    *   @return ARRAY (openIdLink - url OpenId-провайдера, nickname - ник пользователя, email - email пользователя, fullname - полное имя пользователя)
    *    
    *    
    */
    
    public function finalAuth()
    {     
   
        Yii::app()->user->setState('openIdAnswerTime',time());
        
        $consumer = $this->getConsumer();
    
        $returnTo = $this->getReturnTo().'?'.$_SERVER['QUERY_STRING'];             
        
        $response = $consumer->complete($returnTo);        
        
        // нажали "Отмена" на страничке OpenId-провайдера
        if($response->status == Auth_OpenID_CANCEL)
        {
            throw new CHttpException('Авторизация отменена!');
        }
        
        // логин или пароль указаны не правильно
        elseif($response->status == Auth_OpenID_FAILURE)
        {
            throw new CHttpException('OpenId авторизация не удалась!');
        }
        
        // авторизация завершилась успешно...
        elseif($response->status == Auth_OpenID_SUCCESS)
        {             
             $openid = $response->getDisplayIdentifier();
             
             $sregResp = Auth_OpenID_SRegResponse::fromSuccessResponse($response);
             
             $sReg = $sregResp->contents();
             
             $sReg['openIdLink'] = $openid;
             
             return $sReg;
        }
    }

    
}
?>