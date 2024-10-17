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
        try {
            $this->conn = new mysqli($this->servername, $this->username, $this->password, $this->dbname);
            
            if ($this->conn->connect_error) {
                throw new Exception("Conexión fallida: " . $this->conn->connect_error);
            }

            // Establecer el conjunto de caracteres a utf8
            $this->conn->set_charset("utf8");
            
            // Desactivar el autocommit para mejor control de transacciones
            $this->conn->autocommit(true);
            
            return $this->conn;
        } catch (Exception $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }

    public function query($sql) {
        // Limpiar resultados pendientes
        while ($this->conn->more_results()) {
            $this->conn->next_result();
            $this->conn->store_result();
        }

        $result = $this->conn->query($sql);
        if ($result === false) {
            throw new Exception("Error en la consulta: " . $this->conn->error);
        }
        return $result;
    }

    public function prepare($sql) {
        // Limpiar resultados pendientes
        while ($this->conn->more_results()) {
            $this->conn->next_result();
            $this->conn->store_result();
        }

        $stmt = $this->conn->prepare($sql);
        if ($stmt === false) {
            throw new Exception("Error preparando la consulta: " . $this->conn->error);
        }
        return $stmt;
    }

    public function error() {
        return $this->conn->error;
    }

    public function getConnection() {
        return $this->conn;
    }

    public function close() {
        if ($this->conn) {
            // Limpiar resultados pendientes antes de cerrar
            while ($this->conn->more_results()) {
                $this->conn->next_result();
                $this->conn->store_result();
            }
            $this->conn->close();
        }
    }
}
?>