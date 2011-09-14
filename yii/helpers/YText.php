<?php
/**
 *
 *    Хелпер, содержащий самые необходимые функции для работы с текстом
 *    Большинство функций взяты из фреймворка Codeigniter (text_helper)
 *    
 *    @package Yupe
 *    @subpackage helpers
 *    @version 0.0.1
 *    @author  Opeykin A. <aopeykin@gmail.com>
 *        
 *
 *
 */

class YText
{   
    
    public static function characterLimiter($str, $n = 500, $end_char = '&#8230;')
    {
	if (mb_strlen($str) < $n)
	{
		return $str;
	}
	
	$str = preg_replace("/\s+/", ' ', str_replace(array("\r\n", "\r", "\n"), ' ', $str));

	if (mb_strlen($str) <= $n)
	{
		return $str;
	}

	$out = "";
	foreach (explode(' ', trim($str)) as $val)
	{
		$out .= $val.' ';
		
		if (mb_strlen($out) >= $n)
		{
			$out = trim($out);
			return (mb_strlen($out) == mb_strlen($str)) ? $out : $out.$end_char;
		}		
	}
   }
    
    
    
    
    public static  function wordLimiter($str, $limit = 100, $end_char = '&#8230;')
    {
	if (trim($str) == '')
	{
		return $str;
	}

	preg_match('/^\s*+(?:\S++\s*+){1,'.(int) $limit.'}/', $str, $matches);
		
	if (mb_strlen($str) == mb_strlen($matches[0]))
	{
		$end_char = '';
	}
	
	return rtrim($matches[0]).$end_char;
    }
    
    
    
    public static function asciiToEntities($str)
    {
       $count	= 1;
       $out	= '';
       $temp	= array();
    
       for ($i = 0, $s = mb_strlen($str); $i < $s; $i++)
       {
	       $ordinal = ord($str[$i]);
    
	       if ($ordinal < 128)
	       {
			    /*
				    If the $temp array has a value but we have moved on, then it seems only
				    fair that we output that entity and restart $temp before continuing. -Paul
			    */
			    if (count($temp) == 1)
			    {
				    $out  .= '&#'.array_shift($temp).';';
				    $count = 1;
			    }

			    $out .= $str[$i];
	       }
	       else
	       {
		       if (count($temp) == 0)
		       {
			       $count = ($ordinal < 224) ? 2 : 3;
		       }
	    
		       $temp[] = $ordinal;
	    
		       if (count($temp) == $count)
		       {
			       $number = ($count == 3) ? (($temp['0'] % 16) * 4096) + (($temp['1'] % 64) * 64) + ($temp['2'] % 64) : (($temp['0'] % 32) * 64) + ($temp['1'] % 64);

			       $out .= '&#'.$number.';';
			       $count = 1;
			       $temp = array();
		       }
	       }
       }

       return $out;
    }
    
    
    public static function entitiesToAscii($str, $all = TRUE)
    {
       if (preg_match_all('/\&#(\d+)\;/', $str, $matches))
       {
	       for ($i = 0, $s = count($matches['0']); $i < $s; $i++)
	       {				
		       $digits = $matches['1'][$i];

		       $out = '';

		       if ($digits < 128)
		       {
			       $out .= chr($digits);
	    
		       }
		       elseif ($digits < 2048)
		       {
			       $out .= chr(192 + (($digits - ($digits % 64)) / 64));
			       $out .= chr(128 + ($digits % 64));
		       }
		       else
		       {
			       $out .= chr(224 + (($digits - ($digits % 4096)) / 4096));
			       $out .= chr(128 + ((($digits % 4096) - ($digits % 64)) / 64));
			       $out .= chr(128 + ($digits % 64));
		       }

		       $str = str_replace($matches['0'][$i], $out, $str);				
	       }
       }

       if ($all)
       {
	       $str = str_replace(array("&amp;", "&lt;", "&gt;", "&quot;", "&apos;", "&#45;"),
						      array("&","<",">","\"", "'", "-"),
						      $str);
       }

       return $str;
    }
    
    
    /**
    * Word Censoring Function
    *
    * Supply a string and an array of disallowed words and any
    * matched words will be converted to #### or to the replacement
    * word you've submitted.
    *
    * @access	public
    * @param	string	the text string
    * @param	string	the array of censoered words
    * @param	string	the optional replacement value
    * @return	string
    */
    
    public static function wordCensor($str, $censored, $replacement = '')
    {
	    if ( ! is_array($censored))
	    {
		    return $str;
	    }
    
            $str = ' '.$str.' ';

	    // \w, \b and a few others do not match on a unicode character
	    // set for performance reasons. As a result words like über
	    // will not match on a word boundary. Instead, we'll assume that
	    // a bad word will be bookended by any of these characters.
	    $delim = '[-_\'\"`(){}<>\[\]|!?@#%&,.:;^~*+=\/ 0-9\n\r\t]';

	    foreach ($censored as $badword)
	    {
		    if ($replacement != '')
		    {
			    $str = preg_replace("/({$delim})(".str_replace('\*', '\w*?', preg_quote($badword, '/')).")({$delim})/i", "\\1{$replacement}\\3", $str);
		    }
		    else
		    {
			    $str = preg_replace("/({$delim})(".str_replace('\*', '\w*?', preg_quote($badword, '/')).")({$delim})/ie", "'\\1'.str_repeat('#', strlen('\\2')).'\\3'", $str);
		    }
	    }

            return trim($str);
    }
    
    
}
?>