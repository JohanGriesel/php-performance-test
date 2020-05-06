<?php
$TestTypeStr = TestConfig::MEDIUM_LOAD_WITH_DB_STR;

// Azure
$DatabaseHostStr = 'mobility-mysql-server-1.mysql.database.azure.com';
$DatabaseUsernameStr = 'perftest@mobility-mysql-server-1';
$DatabasePasswordStr = '123';
$DatabaseNameStr = 'perftest';
$DatabasePortInt = null;
$DatabaseServerCertPathStr = "azure_mysql_cert.pem"; // JGL: This is entirely optional, but required when trying to connect to a database using SSL

/*
// xneelo
$DatabaseHostStr = 'dedi1260.jnb1.host-h.net';
$DatabaseUsernameStr = 'server_1059';
$DatabasePasswordStr = 'K1sG3UMawSg8YuResPP8';
$DatabaseNameStr = 'perftest';
$DatabasePortInt = null;
$DatabaseServerCertPathStr = null; // JGL: This is entirely optional, but required when trying to connect to a database using SSL
?>