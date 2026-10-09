<?php
// config/db.php
class Database {
    private $host = "localhost";
    private $user = "root";
    private $pass = "";
    private $db   = "schema";
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->db);
            if ($this->conn->connect_error) {
                throw new Exception("Error de conexión: " . $this->conn->connect_error);
            }
            $this->conn->set_charset("utf8mb4");
        } catch (Exception $e) {
            http_response_code(500);
            die(json_encode(["status" => "error", "mensaje" => $e->getMessage()]));
        }
        return $this->conn;
    }
}
?>