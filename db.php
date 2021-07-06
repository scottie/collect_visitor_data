<?php 
class DB
{
    protected $conn = null;
    public function Connect()
    {
        try {

            $dsn = "mysql:dbname=swepsl_leads; host=localhost";
            $user = "YOUR_USER";
            $password = "YOUR_PASS";

            $options  = array(PDO::ATTR_ERRMODE =>      PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            );


            $this->conn = new PDO($dsn, $user, $password, $options);
            return $this->conn;

        } catch (PDOException $e) {
            echo 'Connection error: ' . $e->getMessage();
        }
    }

    public function Close()
    {
        $this->conn = null;
    }
}
?>
