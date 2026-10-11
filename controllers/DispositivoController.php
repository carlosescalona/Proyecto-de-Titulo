<?php
require_once 'config/db.php';
require_once 'models/DispositivoModel.php';

class DispositivoController {
    private $model;

    public function __construct() {
        $database = new Database();
        $this->model = new DispositivoModel($database->getConnection());
    }

    public function index() {
        $dispositivos = $this->model->obtenerTodos();
        $editar = null;
        
        if (isset($_GET['editar_id'])) {
            $editar = $this->model->obtenerPorId($_GET['editar_id']);
        }

        require_once 'views/dispositivos_mantenedor.php';
    }

    public function guardar() {
        $id = $_POST['dispositivo_id'] ?? '';
        $nombre = $_POST['nombre'] ?? '';
        $tipo = $_POST['tipo'] ?? 'Agente Monitoreo';
        $ubicacion = $_POST['ubicacion'] ?? 'Oficina';
        $esEdicion = $_POST['es_edicion'] ?? '0';

        if ($esEdicion === '1') {
            $this->model->actualizarManual($id, $nombre, $tipo, $ubicacion);
        } else {
            $this->model->crearManual($id, $nombre, $tipo, $ubicacion);
        }

        header("Location: index.php?accion=mantenedor_dispositivos");
        exit();
    }

    public function eliminar() {
        $id = $_GET['id'] ?? '';
        if ($id) {
            $this->model->eliminar($id);
        }
        header("Location: index.php?accion=mantenedor_dispositivos");
        exit();
    }
}
?>