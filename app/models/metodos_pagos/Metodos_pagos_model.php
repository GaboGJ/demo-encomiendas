<?php
class Metodos_pagos_model {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function getMetodosPagosActivos() {
        try {
            $sql = "SELECT id_metodo_pago, nombre_metodo_pago  
                    FROM metodos_pagos 
                    WHERE estado_metodo_pago = 1 OR estado_metodo_pago IS NULL 
                    ORDER BY id_metodo_pago ASC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}
?>