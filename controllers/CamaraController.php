<?php
require_once 'config/db.php';
require_once 'models/CamaraModel.php';

class CamaraController {
    private $model;

    public function __construct() {
        $database = new Database();
        $this->model = new CamaraModel($database->getConnection());
    }

    public function index() {
        $camaras = $this->model->obtenerTodas();
        $editar = null;

        if (isset($_GET['editar_id'])) {
            $editar = $this->model->obtenerPorId($_GET['editar_id']);
        }

        require_once 'views/camaras_mantenedor.php';
    }

    public function guardar() {
        $id = $_POST['camara_id'] ?? '';
        $nombre = $_POST['nombre'] ?? '';
        $ip = $_POST['ip_rtsp'] ?? '';
        $ubicacion = $_POST['ubicacion'] ?? '';
        $esEdicion = $_POST['es_edicion'] ?? '0';

        if ($esEdicion === '1') {
            $this->model->actualizarManual($id, $nombre, $ip, $ubicacion);
        } else {
            $this->model->crearManual($id, $nombre, $ip, $ubicacion);
        }

        header("Location: index.php?accion=mantenedor_camaras");
        exit();
    }

    public function eliminar() {
        $id = $_GET['id'] ?? '';
        if ($id) {
            $this->model->eliminar($id);
        }
        header("Location: index.php?accion=mantenedor_camaras");
        exit();
    }
}
?>