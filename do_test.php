<?php
/**
 * Created by PhpStorm.
 * User: Johan Griesel (Stratusolve (Pty) Ltd)
 * Date: 2017/02/18
 * Time: 10:07 AM
 */
session_start();
require("assets/php/controller.php");
if (!isset($TestTypeStr)) {
    $TestTypeStr = TestConfig::LIGHT_LOAD_NO_DB_STR;
}
PerformanceTest::doTestInstance($TestTypeStr);

abstract class PerformanceTest {
    public static function doTestInstance($TestTypeStr = TestConfig::LIGHT_LOAD_NO_DB_STR) {
        OutputManager::startTest();
        switch($TestTypeStr) {
            case TestConfig::LIGHT_LOAD_NO_DB_STR: self::doLightLoadNoDbTest();
                break;
            case TestConfig::LIGHT_LOAD_WITH_DB_STR: self::doLightLoadWithDbTest();
                break;
            case TestConfig::MEDIUM_LOAD_NO_DB_STR:
                for($i=0;$i<10;$i++){self::doLightLoadNoDbTest();}
                break;
            case TestConfig::MEDIUM_LOAD_WITH_DB_STR:
                for($i=0;$i<10;$i++){self::doLightLoadWithDbTest();}
                break;
            case TestConfig::HEAVY_LOAD_NO_DB_STR:
                for($i=0;$i<100;$i++){self::doLightLoadNoDbTest();}
                break;
            case TestConfig::HEAVY_LOAD_WITH_DB_STR:
                for($i=0;$i<100;$i++){self::doLightLoadWithDbTest();}
                break;
            default: self::doLightLoadNoDbTest();
        }
        OutputManager::endTest($TestTypeStr);
    }
    public static function doLightLoadNoDbTest() {
        for ($i=1;$i<=1000;$i++) {
            $Value1Int = $i;
            $Value2Int = rand(1,10000);
            $ResultInt = $Value1Int*$Value2Int;
            OutputManager::writeOutput("Random calculation result: $Value1Int*$Value2Int=$ResultInt");
        }
    }
    public static function doLightLoadWithDbTest() {
        $DBLinkObj = TestConfig::connectDatabase();
        $CurrentTimeStamp = new DateTime();
        $TableNameStr = "phpperftest_".$CurrentTimeStamp->getTimestamp().'_'.rand(1,100000);
        $SqlStr = "
CREATE TABLE IF NOT EXISTS $TableNameStr (
    PerfomanceItemId INT AUTO_INCREMENT,
    PerformanceData TEXT,
    PRIMARY KEY (PerfomanceItemId)
)  ENGINE=INNODB;";
        $DBLinkObj->query($SqlStr);
        OutputManager::writeOutput("Performance test table created: $TableNameStr");

        for ($i=1;$i<=1000;$i++) {
            $RandomTextStr = md5(rand(0,100000));
            $SqlStr = "INSERT INTO `$TableNameStr` (`PerfomanceItemId`, `PerformanceData`) VALUES (NULL, '$RandomTextStr');";
            $DBLinkObj->query($SqlStr);
        }
        $SqlStr = "select * from $TableNameStr";
        $SelectResult = $DBLinkObj->query($SqlStr);
        if ($SelectResult) {
            $MaximumMemoryStr = round(memory_get_peak_usage()/1024/1024,2)."MB";
            OutputManager::writeOutput($SelectResult->num_rows." items added and returned $MaximumMemoryStr");
        }
        $SqlStr = "drop table $TableNameStr";
        $DBLinkObj->query($SqlStr);
        OutputManager::writeOutput("Table $TableNameStr dropped");
        $DBLinkObj->close();
    }
    public static function doMediumLoadNoDbTest() {

    }
    public static function doMediumLoadWithDbTest() {

    }
    public static function doHeavyLoadNoDbTest() {

    }
    public static function doHeavyLoadWithDbTest() {

    }
}