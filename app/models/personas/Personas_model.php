<?php
class Personas_model {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function buscarPorCi($ci) {
        $sql = "SELECT id_persona, carnet_persona, nombre_persona, apellido_paterno_persona, apellido_materno_persona, telefono_persona 
                FROM personas 
                WHERE carnet_persona = :ci";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':ci' => $ci]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function insertarPersona($ci, $nombre, $paterno, $materno = '', $celular = '', $direccion = null) {
        $sqlIns = "INSERT INTO personas (carnet_persona, nombre_persona, apellido_paterno_persona, apellido_materno_persona, telefono_persona, direccion_persona, estado_persona, create_persona) 
                   VALUES (:ci, :nombre, :paterno, :materno, :celular, :direccion, 1, NOW())";
        $stmtIns = $this->pdo->prepare($sqlIns);
        $stmtIns->execute([
            ':ci'       => $ci,
            ':nombre'   => $nombre,
            ':paterno'  => $paterno,
            ':materno'  => $materno,
            ':celular'  => $celular,
            ':direccion'=> $direccion
        ]);

        return $this->pdo->lastInsertId();
    }
}
?>