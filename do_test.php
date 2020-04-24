<?php
/**
 * Created by PhpStorm.
 * User: Johan Griesel (Stratusolve (Pty) Ltd)
 * Date: 2017/02/18
 * Time: 10:07 AM
 */
session_start();
header("Content-Type: text/plain");
require("assets/php/controller.php");
if (isset($_GET['test_type'])) {
    $TestTypeStr = $_GET['test_type'];
}
if (!isset($TestTypeStr)) {
    $TestTypeStr = TestConfig::LIGHT_LOAD_NO_DB_STR;
}
$TestNameStr = null;
if (isset($_GET['test_name'])) {
    $TestNameStr = $_GET['test_name'];
}
PerformanceTest::doTestInstance($TestTypeStr,$TestNameStr);

abstract class PerformanceTest {
    protected static $ConnectionCount = 0;
    protected static $ConnectionTotalDelay = 0;
    public static function doTestInstance($TestTypeStr = TestConfig::LIGHT_LOAD_NO_DB_STR,
                                            $TestNameStr = null) {
        OutputManager::startTest($TestNameStr);
        switch($TestTypeStr) {
            case TestConfig::LIGHT_LOAD_NO_DB_STR: self::doLightLoadNoDbTest();
                break;
            case TestConfig::LIGHT_LOAD_WITH_DB_STR: self::doLightLoadWithDbTest();
                break;
            case TestConfig::MEDIUM_LOAD_NO_DB_STR:
                for($i=0;$i<5;$i++){self::doLightLoadNoDbTest();}
                break;
            case TestConfig::MEDIUM_LOAD_WITH_DB_STR:
                for($i=0;$i<10;$i++){self::doLightLoadWithDbTest(100);}
                break;
            case TestConfig::HEAVY_LOAD_NO_DB_STR:
                for($i=0;$i<15;$i++){self::doLightLoadNoDbTest();}
                break;
            case TestConfig::HEAVY_LOAD_WITH_DB_STR:
                for($i=0;$i<10;$i++){self::doLightLoadWithDbTest(1000);}
                break;
            default: self::doLightLoadNoDbTest();
        }
        $ConnectionDelayAverage = round(self::$ConnectionTotalDelay / self::$ConnectionCount,3);
        OutputManager::writeOutput("Database connection delay average: $ConnectionDelayAverage s");
        OutputManager::endTest($TestTypeStr,$TestNameStr);
    }
    public static function doLightLoadNoDbTest() {
        for ($i=1;$i<=1000;$i++) {
            $Value1Int = $i;
            $Value2Int = rand(1,10000);
            $ResultInt = $Value1Int*$Value2Int;
            OutputManager::writeOutput("Random calculation result: $Value1Int*$Value2Int=$ResultInt");
        }
    }
    public static function doLightLoadWithDbTest($DbRowsToAdd = 10) {
        $ConnectStartTime = microtime(true);
        $DBLinkObj = TestConfig::connectDatabase();
        $ConnectEndTime = microtime(true);
        $DurationInSecondsFloat = round(($ConnectEndTime - $ConnectStartTime),3);
        self::$ConnectionCount++;
        self::$ConnectionTotalDelay += $DurationInSecondsFloat;
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

        for ($i=1;$i<=$DbRowsToAdd;$i++) {
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