<?php
namespace Sentia\Utils;

use App\Service\ParametersApp;
use PDO;

class PdoUtil
{
    public ?PDO $pdo = null;

    /**
     * PdoUtil constructor.
     */
    public function __construct(ParametersApp $parametersApp){
        if($this->pdo === null){
            // parse db connection data for PDO
            $parts = explode('@', $parametersApp->databaseUrl);
            $loginAndPasswordArray = explode(':', str_replace('mysql://', '', $parts[0]));
            $login = $loginAndPasswordArray[0];
            $password = $loginAndPasswordArray[1];

            $urlAndDbArray = explode('/', $parts[1]);
            $url = $urlAndDbArray[0];
            $dbName = $urlAndDbArray[1];

            $this->pdo = new PDO('mysql:host='.$url.';dbname='.$dbName.';charset=utf8mb4', $login, $password);
        }
    }
}
