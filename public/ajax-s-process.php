<?php

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'lk_database');

define('APPROOT', dirname(dirname(__FILE__)).'/');

class Database {
    protected $database;
    public $isConnected;

    public function __construct($host = DB_HOST, $user = DB_USER, $password = DB_PASS, $database = DB_NAME) {
        $this->isConnected = true;

        try {
            $this->database = new PDO("mysql:host={$host};dbname={$database}", $user, $password);
            $this->database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->database->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function Disconnect() {
        $this->isConnected = false;
        $this->database = null;
    }

    public function GetRows($query, $params = []) {
        try {
            $stmt = $this->database->prepare($query);
            $stmt->execute($params);

            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception($e->getMessage());
        }
    }
}

$db = new Database();

if (isset($_POST)) {
    $output = '';
    $input = $_POST['i'];

    $result = $db->GetRows("SELECT * FROM index_site_data WHERE (name LIKE '%{$input}%' OR op1 LIKE '%{$input}%')");

    if ($result) {
        $output = '';

        foreach ($result as $r) {
            $name = $r['name'];
            $op1 = $r['op1'];
            $type = $r['type'];
            $ch_name = $op1.': '.$name;
            if ($type == 'character') {
                $name = $ch_name;
            }
            $output .= '
                <div class="resultrow">
                <a href="'.findUrlStructure($type, $op1).'">
                    <div class="title"><span class="badge badge-secondary">'.$type.'</span>'.$name.'</div>
                </a>
                </div>
            ';
        }
        echo $output;
    } else {
        echo 'No results were found.';
    }
}

function findUrlStructure($type, $key) {
    $item = '/world/items?name=';
    $character = '/character/';
    $user = '/user/';
    $page = '/info/';
    $pet = '/world/pets/';
    $prompt = '/prompts/';

    $domain = $_SERVER['SERVER_NAME'];

    return ${$type}.$key;
}
