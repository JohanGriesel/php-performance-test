<?php
require("assets/php/config.php");

abstract class TestConfig {
    const LIGHT_LOAD_NO_DB_STR = 'LIGHT_LOAD_NO_DB';
    const MEDIUM_LOAD_NO_DB_STR = 'MEDIUM_LOAD_NO_DB';
    const HEAVY_LOAD_NO_DB_STR = 'HEAVY_LOAD_NO_DB';
    const LIGHT_LOAD_WITH_DB_STR = 'LIGHT_LOAD_WITH_DB';
    const MEDIUM_LOAD_WITH_DB_STR = 'MEDIUM_LOAD_WITH_DB';
    const HEAVY_LOAD_WITH_DB_STR = 'HEAVY_LOAD_WITH_DB';

    public static function connectDatabase() {
        global $DatabaseHostStr, $DatabaseUsernameStr, $DatabasePasswordStr, $DatabaseNameStr, $DatabasePortInt, $DatabaseServerCertPathStr;
        $LinkObj = mysqli_init();
        mysqli_ssl_set($LinkObj, NULL, NULL, $DatabaseServerCertPathStr, NULL, NULL);
        mysqli_real_connect($LinkObj, $DatabaseHostStr, $DatabaseUsernameStr, $DatabasePasswordStr, $DatabaseNameStr, $DatabasePortInt);
        if (mysqli_connect_errno()) {
            OutputManager::writeOutput('Failed to connect to MySQL: '.mysqli_connect_error());
            return null;
        }
        return $LinkObj;
    }
    public static function getDataBaseServer() {
        global $DatabaseHostStr;
        return $DatabaseHostStr;
    }
}
abstract class OutputManager {
    public static function setOutputFile($TestNameStr = null) {
        $CurrentTimeStamp = new DateTime();
        if (is_null($TestNameStr)) {
            $TestNameStr = 'UnnamedTests';
        }
        $TestDirectory = 'assets/outputs/'.$TestNameStr.'/';
        if (!is_dir($TestDirectory)) {
            mkdir($TestDirectory);
        }
        $RequestPrepend = 'unknown_host';
        if (isset($_SERVER['REMOTE_ADDR'])) {
            $RequestPrepend = str_replace(".", "_", $_SERVER['REMOTE_ADDR']);
        }
        $_SESSION["output_file"] = "$TestDirectory".$RequestPrepend."-".$CurrentTimeStamp->format('d-m-Y_H:i:s')."_".rand(1,1000).'.txt';
    }
    public static function getOutputFile() {
        return $_SESSION["output_file"];
    }
    public static function startTest($TestNameStr = null) {
        self::setOutputFile($TestNameStr);
        if (is_null($TestNameStr)) {
            $TestNameStr = 'UnnamedTests';
        }
        self::writeOutput("Starting test: $TestNameStr",false,true);
        $_SESSION['start_time'] = microtime(true);
    }
    public static function endTest($TestTypeStr = TestConfig::LIGHT_LOAD_NO_DB_STR,$TestNameStr = null) {
        $ServerValueArray = [
            "SERVER_ADDR",
            "SERVER_PORT",
            "REMOTE_ADDR",
            "HTTP_HOST",
            "HTTP_USER_AGENT",
            "REQUEST_METHOD",
            "QUERY_STRING",
            "REQUEST_URI"];
        $RequestInfoStr = '';
        foreach($ServerValueArray as $item) {
            if (isset($_SERVER[$item])) {
                $RequestInfoStr .= $item." => ".$_SERVER[$item]."\r\n";
            }
        }
        if (is_null($TestNameStr)) {
            $TestNameStr = 'UnnamedTests';
        }
        $MaximumMemoryStr = round(memory_get_peak_usage()/1024/1024,2)."MB";
        $_SESSION['end_time'] = microtime(true);
        $DurationInSecondsFloat = round(($_SESSION['end_time'] - $_SESSION['start_time']),3);
        self::writeOutput("Test finished ($TestNameStr). Summary:\r\nTest Type: $TestTypeStr\r\nTest Duration: $DurationInSecondsFloat s\r\nMaximum memory allocated: $MaximumMemoryStr\r\nRequest info:\r\n$RequestInfoStr\r\nTest detail:",true,false);
        echo file_get_contents(self::getOutputFile());
    }
    public static function writeOutput($Message,$PrependMessageBool = false,$ClearExistingContentBool = false) {
        $ExistingContentStr = '';
        if (!$ClearExistingContentBool) {
            $ExistingContentStr = file_get_contents(self::getOutputFile());
        }
        $CurrentDateObj = new DateTime();
        $FinalMessageStr = $CurrentDateObj->format('d-m-Y H:i:s').": $Message";
        if ($PrependMessageBool) {
            $FinalContentStr = "$FinalMessageStr\r\n$ExistingContentStr";
        } else {
            $FinalContentStr = "$ExistingContentStr\r\n$FinalMessageStr";
        }
        file_put_contents(self::getOutputFile(), $FinalContentStr);
    }
}
?>



