<?php

use App\Models\IndexSiteData;

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

    $result = $db->GetRows("SELECT * FROM site_index WHERE (title LIKE '%{$input}%' OR description LIKE '%{$input}%')");

    //$test = testMe();

    if ($result) {
        $output = '';

        foreach ($result as $r) {
            $name = $r['title'];
            $key = $r['identifier'];
            $type = $r['type'];
            $output .= '
                <div class="resultrow">
                <a href="'.findUrlStructure($type, $key).'">
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
    $search = strtolower($type);

    $item = '/world/items?name=';
    $character = '/character/';
    $user = '/user/';
    $page = '/info/';
    $pet = '/world/pets/';
    $prompt = '/prompts/';
    $shop = '/shops/';

    $domain = $_SERVER['SERVER_NAME'];

    return ${$search}.$key;
}
