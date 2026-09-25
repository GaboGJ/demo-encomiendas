<?php
class Sucursales_model {
    private $pdo;

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    /**
     * Obtiene las sucursales/agencias destino excluyendo la de origen
     */
    public function getSucursalesDestino($id_sucursal_origen) {
        try {
            $sql = "SELECT id_sucursal, ciudad_sucursal, nombre_sucursal 
                    FROM sucursales 
                    WHERE id_sucursal != :id_origen AND estado_sucursal = 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':id_origen' => $id_sucursal_origen]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}
?>