<?php
class Database {
  private $servername;
  private $username;
  private $password;
  private $dbname;
  private $conn;

  public function __construct() {
    $this->servername = "localhost";
    $this->username = "root";
    $this->password = "";
    $this->dbname = "scire";
  }

  public function connect() {
    $this->conn = new mysqli($this->servername, $this->username, $this->password, $this->dbname);
    if ($this->conn->connect_error) {
      die("Conexión fallida: " . $this->conn->connect_error);
    }
  }

  public function query($sql) {
    return $this->conn->query($sql);
  }

  public function prepare($sql) {
    return $this->conn->prepare($sql);
  }

  public function close() {
    $this->conn->close();
  }
}
?>