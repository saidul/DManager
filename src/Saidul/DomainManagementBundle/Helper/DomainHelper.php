<?php
namespace Saidul\DomainManagementBundle\Helper;
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Domain helper will provide the methods for managing domain entries in windows hosts file.
 *
 * @author TOSHIBA
 */
class DomainHelper {        
    private static $hostFile = "C:\\Windows\\System32\\drivers\\etc\\hosts";
    
    /**
     * returns an array of ip and their corresponding domain name they belongs to
     * @return array
     */
    public static function findAllDomains(){
        $content = file_get_contents(DomainHelper::$hostFile);
        
        $lines = explode("\r\n",$content);
        foreach($lines as &$l)
        {
            $a = explode("\t",$l);
            if(count($a) < 2) unset($l);
            else{
                $l = array(
                    'ip'=>$a[0],
                    'host'=>$a[1]
                );
            }
        }
        return $lines;
    }
    
    /**
     * Adds a new domain record into the host file
     * @param type $host
     * @param type $ip 
     */
    public static function addDomain($host,$ip="127.0.0.1"){
        $content = file_get_contents(DomainHelper::$hostFile);
        
        $lines = explode("\r\n",$content);
        $lines[] = "{$ip}\t{$host}";
        
        $content = implode("\r\n",$lines);
        file_put_contents(DomainHelper::$hostFile, $content);
    }
    
}

?>
