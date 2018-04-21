<?php

class Checker
{
    /**
     * @var array
     */
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function testAndLog()
    {
        $db = $this->connectToDb(
            $this->config['dbHost'],
            $this->config['dbUser'],
            $this->config['dbPass'],
            $this->config['dbName']
        );

        $result = $this->testConnection($this->config['testHost'], $this->config['testPort']);

        $this->logResult($db, $result);

        return $result;
    }

    public function testConnection(string $host, int $port)
    {
        $date = new DateTime('NOW', new DateTimeZone('Etc/UCT'));
        $errorCode = null;
        $errorString = null;
        $success = false;
        $timeout = 5;

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

    public function connectToDb(string $host, string $user, string $pass, string $name)
    {
        $dbh = new PDO("mysql:host={$host};dbname={$name}", $user, $pass);
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $dbh;
    }

    public function logResult(PDO $db, array $result)
    {
        $query = $db->prepare('INSERT INTO logs (date, success, time, errorCode, errorString) VALUES (?, ?, ?, ? ,?)');
        $query->execute(
            [
                $result['date'],
                $result['success'],
                $result['time'],
                $result['errorCode'],
                $result['errorString'],
            ]
        );
    }
}
