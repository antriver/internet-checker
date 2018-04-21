<?php

$config = require __DIR__.'/config.php';

function testConnection(string $host, int $port)
{
    $date = new DateTime('NOW', new DateTimeZone('Etc/UCT'));
    $errorCode = null;
    $errorString = null;
    $success = false;
    $timeout = 30;

    $start = microtime(true);
    try {
        if ($fp = fsockopen($host, $port, $errorCode, $errorString, $timeout)) {
            $success = true;
            $errorCode = null;
            $errorString = null;
        }
        fclose($fp);
    } catch (Throwable $exception) {
        $success = false;
        $errorString = $exception->getMessage();
    }

    $time = microtime(true) - $start;

    return [
        'date' => $date->format('Y-m-d H:i:s'),
        'errorCode' => $errorCode,
        'errorString' => $errorString,
        'success' => $success,
        'time' => $time,
    ];
}

function connectToDb(string $host, string $user, string $pass, string $name)
{
    $dbh = new PDO("mysql:host={$host};dbname={$name}", $user, $pass);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    return $dbh;
}

function logResult(PDO $db, array $result)
{
    $query = $db->prepare('INSERT INTO logs (date, success, time, errorCode, errorString) VALUES (?, ?, ?, ? ,?)');
    $query->execute(array_values($result));
}

$db = connectToDb($config['dbHost'], $config['dbUser'], $config['dbPass'], $config['dbName']);

$result = testConnection($config['testHost'], $config['testPort']);

logResult($db, $result);
