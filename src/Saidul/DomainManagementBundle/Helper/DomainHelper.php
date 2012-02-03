<?php
namespace Saidul\DomainManagementBundle\Helper;

/**
 * Domain helper will provide the methods for managing domain entries in windows hosts file.
 *
 * @author Saidul, Nizam
 */
class DomainHelper {        
    private static $hostFile = "C:\\Windows\\System32\\drivers\\etc\\hosts";
    
    /**
     * returns an array of ip and their corresponding domain name they belongs to
     * @return array
     */
    public static function findAllDomains($excludeCommentLines = true){
        $content = file_get_contents(DomainHelper::$hostFile);
        
        $lines = explode("\r\n",$content);
        //echo "<pre>"; print_r($lines); die();
        foreach($lines as $key => &$l)
        {
            $matches = array();
            if(preg_match('/[\s]*(?<disabled>#)?[\s]*(?<ip>[\d]{1,3}\.[\d]{1,3}\.[\d]{1,3}\.[\d]{1,3})[\s]*(?<host>[\w\d-.]+)[\s]*(?<comment>#.*)?/i',$l,$matches))
            {
                $l = array(
                    'record' => true,
                    'disabled' => isset($matches['disabled']) && $matches['disabled']=='#',
                    'ip'      => $matches['ip'],
                    'host'    => $matches['host'],
                    'comment' => isset($matches['comment']) ? $matches['comment'] : null,
                    'local'   => preg_match('/[\w\-\.]+.localhost.com/i',$matches['host']) != 0 ? true: false,
                );
            }else if(preg_match('/[\s]*(?<comment>#.*)/i',$l,$matches))
            {
                if($excludeCommentLines) unset($lines[$key]);
                else{
                    $l = array(
                        'lineComment' => true,
                        'comment' => $matches['comment'],
                        'disabled'  => true,
                        'host'    => null,
                        'ip'    => null,
                        'local' => false
                    );
                }
            }else{
                unset($lines[$key]); //Neither a comment nor a record so remove it
            }
        }
        return $lines;
    }
    public static function getInfoByHostName($host){
        $domainList = self::findAllDomains();
        foreach($domainList as $r){
            if($r['host'] == $host){
                return $r;
            }
        }
        return null;
    }

    /**
     * Updates Domain Record by give index
     * @static
     * @param $idx
     * @param $host
     * @param string $ip
     * @return bool
     */
    public static function updateDomainRecordByIndex($idx,$host,$ip="127.0.0.1"){
        throw new \Exception("Use of depricated method");
        $domainList = self::findAllDomains();
        if(isset($domainList[$idx])){
            $domainList[$idx]['ip'] = $ip;
            $domainList[$idx]['host'] = $host;

            return self::saveToFile($domainList);
        }
        return false;
    }

    public static function updateDomainRecordByHostName($oldHost,$host,$ip="127.0.0.1",$comment=null){
        $domainList = self::findAllDomains(true);
        if($comment){
            $comment = trim($comment);
            if(substr($comment,0,1) != '#') $comment = "#{$comment}";
        }
        foreach($domainList as &$r){
            if($r['host'] == $oldHost){
                $r['ip'] = $ip;
                $r['host'] = $host;
                if($comment) $r['comment'] = $comment;
                return self::saveToFile($domainList);
            }
        }
        return false;
    }
    /**
     * Removes a record using the given index
     * @static
     * @param $idx
     * @return bool
     */
    public static function removeRecordByIdx($idx){
        throw new \Exception("Use of depricated method");
        $domainList = self::findAllDomains();
        if(!isset($domainList[$idx])) return false;
        array_splice($domainList,$idx,1);
        return self::saveToFile($domainList);
    }
    public static function removeRecordByHostName($host){
        $domainList = self::findAllDomains(true);
        foreach($domainList as $key=>$r){
            if($r['host'] == $host){
                unset($domainList[$key]);

                return self::saveToFile($domainList);
            }
        }
        return false;
    }
    /**
     * Save [ip,host] records to host file
     * @static
     * @param $records
     * @return bool
     */
    public static function saveToFile($records){
        foreach($records as &$r){
            if(isset($r['lineComment']) && $r['lineComment']==true){
                $r = $r['comment'];
            }else if(isset($r['record']) && $r['record'] == true){
                if($r['disabled']) $r['ip'] = '# '.$r['ip'];
                $r = implode("\t",array(
                    $r['ip'],$r['host'],$r['comment']
                ));
            }
            if(!is_string($r)) throw new \InvalidArgumentException('Expecting string, got '. gettype($r) );

            //$r = implode("\t",$r);
        }
        $content = implode("\r\n",$records);
        return file_put_contents(self::$hostFile, $content);
    }
    /**
     * Adds a new domain record into the host file
     * @param type $host
     * @param string $ip
     * @return boolean
     */
    public static function addDomain($host,$ip="127.0.0.1", $comment=null, $disabled = false){
        if(preg_match('/(?<ip>[\d]{1,3}\.[\d]{1,3}\.[\d]{1,3}\.[\d]{1,3})/i',$ip) == 0) throw new \Exception("$ip is not an IP");

        $content = file_get_contents(DomainHelper::$hostFile);

        if($comment){
            $comment = trim($comment);
            if(substr($comment,0,1) != '#') $comment = "#{$comment}";
        }

        $lines = explode("\r\n",$content);
        if($disabled) $ip = "# {$ip}";
        $lines[] = implode("\t",array(
            $ip, $host, $comment
        ));
        
        $content = implode("\r\n",$lines);
        return file_put_contents(DomainHelper::$hostFile, $content);
    }
    
}

?>
