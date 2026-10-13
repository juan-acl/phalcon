<?php

namespace App\Controllers;

use App\Library\Reportes\ReporteService;
use App\Library\Uploads\FotoUploader;
use App\Models\Alumno;
use App\Models\Carrera;
use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\Validator\Date;
use Phalcon\Filter\Validation\Validator\PresenceOf;
use Phalcon\Filter\Validation\Validator\StringLength;

/**
 * Controlador para la gestión completa de alumnos:
 * listado con búsqueda, alta con fotografía, edición, ficha y eliminación.
 */
class AlumnosController extends BaseController
{
    public function indexAction(): void
    {
        $rawQ = $this->request->getQuery('q');
        $q = is_string($rawQ) ? trim($rawQ) : '';
        if (mb_strlen($q) > 200) {
            $q = mb_substr($q, 0, 200);
        }
        $this->view->setVar('q', $q);

        if ($q !== '') {
            $alumnos = Alumno::find([
                'conditions' => 'nombres LIKE :q: OR apellidos LIKE :q:',
                'bind'       => ['q' => '%' . $q . '%'],
                'order'      => 'apellidos ASC, nombres ASC',
                'limit'      => 500,
            ]);
        } else {
            $alumnos = Alumno::find([
                'order' => 'apellidos ASC, nombres ASC',
                'limit' => 500,
            ]);
        }

        $this->view->setVar('alumnos', $alumnos);
        $this->view->pick('alumnos/index');
    }

    public function nuevoAction(): void
    {
        $this->view->setVar('carreras', Carrera::find(['order' => 'nombre ASC']));
        $this->view->setVar('old', []);
        $this->view->setVar('errores', []);
        $this->view->pick('alumnos/nuevo');
    }

    public function crearAction()
    {
        $rawNombres = $this->request->getPost('nombres');
        $rawApellidos = $this->request->getPost('apellidos');
        $rawFecha = $this->request->getPost('fecha_nacimiento');
        $rawCarrera = $this->request->getPost('carrera_id');

        $errores = [];
        if (!is_string($rawNombres) || !is_string($rawApellidos) || !is_string($rawFecha)) {
            $errores[] = 'Los datos enviados deben ser cadenas de texto';
        }

        $datos = [
            'nombres'          => is_string($rawNombres) ? trim($rawNombres) : '',
            'apellidos'        => is_string($rawApellidos) ? trim($rawApellidos) : '',
            'fecha_nacimiento' => is_string($rawFecha) ? trim($rawFecha) : '',
            'carrera_id'       => is_numeric($rawCarrera) ? (int) $rawCarrera : 0,
        ];

        $v = new Validation();
        $v->add('nombres', new PresenceOf(['message' => 'Los nombres son obligatorios']));
        $v->add('nombres', new StringLength(['max' => 100, 'messageMaximum' => 'Máximo 100 caracteres en nombres']));
        $v->add('apellidos', new PresenceOf(['message' => 'Los apellidos son obligatorios']));
        $v->add('apellidos', new StringLength(['max' => 100, 'messageMaximum' => 'Máximo 100 caracteres en apellidos']));
        $v->add('fecha_nacimiento', new PresenceOf(['message' => 'La fecha de nacimiento es obligatoria']));
        $v->add('fecha_nacimiento', new Date(['format' => 'Y-m-d', 'message' => 'Fecha de nacimiento inválida']));

        $errores = [];
        foreach ($v->validate($datos) as $mensaje) {
            $errores[] = $mensaje->getMessage();
        }

        if ($datos['fecha_nacimiento'] !== '' && strtotime($datos['fecha_nacimiento']) >= time()) {
            $errores[] = 'La fecha de nacimiento debe ser pasada';
        }

        $carreraExiste = Carrera::findFirst([
            'conditions' => 'id = :id:',
            'bind'       => ['id' => $datos['carrera_id']],
        ]);
        if (!$carreraExiste) {
            $errores[] = 'Selecciona una carrera válida';
        }

        // Subida de fotografía (opcional)
        $rutaFoto = null;
        $uploader = new FotoUploader($this->config['app']['uploadsDir']);

        if ($this->request->hasFiles(false)) {
            foreach ($this->request->getUploadedFiles(false) as $archivo) {
                if ($archivo->getKey() === 'foto' && $archivo->getError() !== UPLOAD_ERR_NO_FILE) {
                    [$rutaFoto, $errorFoto] = $uploader->guardar($archivo);
                    if ($errorFoto) {
                        $errores[] = $errorFoto;
                    }
                }
            }
        }

        if (!empty($errores)) {
            if ($rutaFoto) {
                $uploader->eliminar($rutaFoto);
            }
            $this->conErrores($errores, $datos);
            $this->view->setVar('carreras', Carrera::find(['order' => 'nombre ASC']));
            $this->view->pick('alumnos/nuevo');
            return;
        }

        $alumno = new Alumno();
        $alumno->assign($datos, ['nombres', 'apellidos', 'fecha_nacimiento', 'carrera_id']); // lista blanca
        $alumno->foto = $rutaFoto;

        if (!$alumno->save()) {
            if ($rutaFoto) {
                $uploader->eliminar($rutaFoto);
            }
            $mensajes = array_map(fn($m) => $m->getMessage(), iterator_to_array($alumno->getMessages()));
            $this->conErrores($mensajes, $datos);
            $this->view->setVar('carreras', Carrera::find(['order' => 'nombre ASC']));
            $this->view->pick('alumnos/nuevo');
            return;
        }

        $this->flashSession->success('Alumno registrado correctamente.');
        return $this->irA('alumnos');
    }

    public function fichaAction()
    {
        $formatoRaw = $this->request->getQuery('formato');
        if ($formatoRaw !== null) {
            if (!is_string($formatoRaw) || strtolower($formatoRaw) !== 'pdf') {
                $this->dispatcher->forward(['controller' => 'error', 'action' => 'notFound']);
                return;
            }
        }

        return $this->generarFichaPdf();
    }

    public function verAction()
    {
        $formatoRaw = $this->request->getQuery('formato');
        if ($formatoRaw !== null) {
            if (!is_string($formatoRaw) || strtolower($formatoRaw) !== 'pdf') {
                $this->dispatcher->forward(['controller' => 'error', 'action' => 'notFound']);
                return;
            }
            return $this->generarFichaPdf();
        }

        $id = (int) $this->dispatcher->getParam('id');
        if ($this->esAlumno() && $id !== $this->alumnoId()) {
            $this->dispatcher->forward(['controller' => 'error', 'action' => 'notFound']);
            return;
        }

        $alumno = Alumno::findFirst([
            'conditions' => 'id = :id:',
            'bind'       => ['id' => $id],
        ]);

        if (!$alumno) {
            $this->dispatcher->forward(['controller' => 'error', 'action' => 'notFound']);
            return;
        }

        $service = new ReporteService($this->db, (int) $this->config['app']['notaAprobacion']);
        $historial = $service->historial($id, $this->esAlumno());

        $this->view->setVar('alumno', $alumno);
        $this->view->setVar('historial', $historial);
        $this->view->setVar('umbral', (int) $this->config['app']['notaAprobacion']);
        $this->view->pick('alumnos/ver');
    }

    private function generarFichaPdf()
    {
        $id = (int) $this->dispatcher->getParam('id');
        if ($this->esAlumno() && $id !== $this->alumnoId()) {
            $this->dispatcher->forward(['controller' => 'error', 'action' => 'notFound']);
            return;
        }

        $alumno = Alumno::findFirst([
            'conditions' => 'id = :id:',
            'bind'       => ['id' => $id],
        ]);

        if (!$alumno) {
            $this->dispatcher->forward(['controller' => 'error', 'action' => 'notFound']);
            return;
        }

        $umbral = (int) $this->config['app']['notaAprobacion'];
        $service = new ReporteService($this->db, $umbral);
        $historial = $service->historial($id, $this->esAlumno());

        $carrera = Carrera::findFirst($alumno->carrera_id);
        $total = count($historial);
        $totalAprobados = count(array_filter($historial, fn($h) => !empty($h['aprobado'])));
        $totalReprobados = $total - $totalAprobados;
        $promedio = $total > 0 ? number_format(array_sum(array_column($historial, 'nota')) / $total, 2) : '0.00';

        $fotoPath = '';
        if (!empty($alumno->foto) && is_file(BASE_PATH . '/public/' . $alumno->foto)) {
            $fotoPath = BASE_PATH . '/public/' . $alumno->foto;
        }

        $html = $this->simpleView->render('reportes/pdf/ficha-alumno', [
            'alumno'          => $alumno,
            'carreraNombre'   => $carrera ? $carrera->nombre : 'Sin carrera asignada',
            'fotoPath'        => $fotoPath,
            'historial'       => $historial,
            'umbral'          => $umbral,
            'totalAprobados'  => $totalAprobados,
            'totalReprobados' => $totalReprobados,
            'promedio'        => $promedio,
            'fechaHora'       => date('d/m/Y H:i'),
        ]);

        return $this->exportService->pdf($html, 'ficha-alumno-' . $alumno->id, 'portrait');
    }

    public function editarAction(): void
    {
        $id = (int) $this->dispatcher->getParam('id');
        $alumno = Alumno::findFirst([
            'conditions' => 'id = :id:',
            'bind'       => ['id' => $id],
        ]);

        if (!$alumno) {
            $this->dispatcher->forward(['controller' => 'error', 'action' => 'notFound']);
            return;
        }

        $this->view->setVar('alumno', $alumno);
        $this->view->setVar('carreras', Carrera::find(['order' => 'nombre ASC']));
        $this->view->setVar('old', [
            'nombres'          => $alumno->nombres,
            'apellidos'        => $alumno->apellidos,
            'fecha_nacimiento' => $alumno->fecha_nacimiento,
            'carrera_id'       => $alumno->carrera_id,
        ]);
        $this->view->setVar('errores', []);
        $this->view->pick('alumnos/editar');
    }

    public function actualizarAction()
    {
        $id = (int) $this->dispatcher->getParam('id');
        $alumno = Alumno::findFirst([
            'conditions' => 'id = :id:',
            'bind'       => ['id' => $id],
        ]);

        if (!$alumno) {
            $this->dispatcher->forward(['controller' => 'error', 'action' => 'notFound']);
            return;
        }

        $rawNombres = $this->request->getPost('nombres');
        $rawApellidos = $this->request->getPost('apellidos');
        $rawFecha = $this->request->getPost('fecha_nacimiento');
        $rawCarrera = $this->request->getPost('carrera_id');

        $errores = [];
        if (!is_string($rawNombres) || !is_string($rawApellidos) || !is_string($rawFecha)) {
            $errores[] = 'Los datos enviados deben ser cadenas de texto';
        }

        $datos = [
            'nombres'          => is_string($rawNombres) ? trim($rawNombres) : '',
            'apellidos'        => is_string($rawApellidos) ? trim($rawApellidos) : '',
            'fecha_nacimiento' => is_string($rawFecha) ? trim($rawFecha) : '',
            'carrera_id'       => is_numeric($rawCarrera) ? (int) $rawCarrera : 0,
        ];

        $v = new Validation();
        $v->add('nombres', new PresenceOf(['message' => 'Los nombres son obligatorios']));
        $v->add('nombres', new StringLength(['max' => 100, 'messageMaximum' => 'Máximo 100 caracteres en nombres']));
        $v->add('apellidos', new PresenceOf(['message' => 'Los apellidos son obligatorios']));
        $v->add('apellidos', new StringLength(['max' => 100, 'messageMaximum' => 'Máximo 100 caracteres en apellidos']));
        $v->add('fecha_nacimiento', new PresenceOf(['message' => 'La fecha de nacimiento es obligatoria']));
        $v->add('fecha_nacimiento', new Date(['format' => 'Y-m-d', 'message' => 'Fecha de nacimiento inválida']));

        foreach ($v->validate($datos) as $mensaje) {
            $errores[] = $mensaje->getMessage();
        }

        if ($datos['fecha_nacimiento'] !== '' && strtotime($datos['fecha_nacimiento']) >= time()) {
            $errores[] = 'La fecha de nacimiento debe ser pasada';
        }

        $carreraExiste = Carrera::findFirst([
            'conditions' => 'id = :id:',
            'bind'       => ['id' => $datos['carrera_id']],
        ]);
        if (!$carreraExiste) {
            $errores[] = 'Selecciona una carrera válida';
        }

        // Subida de nueva foto (opcional)
        $nuevaFoto = null;
        $uploader = new FotoUploader($this->config['app']['uploadsDir']);

        if ($this->request->hasFiles(false)) {
            foreach ($this->request->getUploadedFiles(false) as $archivo) {
                if ($archivo->getKey() === 'foto' && $archivo->getError() !== UPLOAD_ERR_NO_FILE) {
                    [$nuevaFoto, $errorFoto] = $uploader->guardar($archivo);
                    if ($errorFoto) {
                        $errores[] = $errorFoto;
                    }
                }
            }
        }

        if (!empty($errores)) {
            if ($nuevaFoto) {
                $uploader->eliminar($nuevaFoto);
            }
            $this->conErrores($errores, $datos);
            $this->view->setVar('alumno', $alumno);
            $this->view->setVar('carreras', Carrera::find(['order' => 'nombre ASC']));
            $this->view->pick('alumnos/editar');
            return;
        }

        $fotoAnterior = $alumno->foto;
        $alumno->assign($datos, ['nombres', 'apellidos', 'fecha_nacimiento', 'carrera_id']); // lista blanca
        if ($nuevaFoto) {
            $alumno->foto = $nuevaFoto;
        }

        if (!$alumno->save()) {
            if ($nuevaFoto) {
                $uploader->eliminar($nuevaFoto);
            }
            $mensajes = array_map(fn($m) => $m->getMessage(), iterator_to_array($alumno->getMessages()));
            $this->conErrores($mensajes, $datos);
            $this->view->setVar('alumno', $alumno);
            $this->view->setVar('carreras', Carrera::find(['order' => 'nombre ASC']));
            $this->view->pick('alumnos/editar');
            return;
        }

        // Si se subió una nueva foto y se guardó el modelo con éxito, borrar la foto vieja
        if ($nuevaFoto && $fotoAnterior) {
            $uploader->eliminar($fotoAnterior);
        }

        $this->flashSession->success('Alumno actualizado correctamente.');
        return $this->irA('alumnos');
    }

    public function eliminarAction()
    {
        $id = (int) $this->dispatcher->getParam('id');
        $alumno = Alumno::findFirst([
            'conditions' => 'id = :id:',
            'bind'       => ['id' => $id],
        ]);

        if (!$alumno) {
            $this->dispatcher->forward(['controller' => 'error', 'action' => 'notFound']);
            return;
        }

        $foto = $alumno->foto;

        if (!$alumno->delete()) {
            $this->flashSession->error('No se pudo eliminar al alumno.');
        } else {
            if ($foto) {
                (new FotoUploader($this->config['app']['uploadsDir']))->eliminar($foto);
            }
            $this->flashSession->success('Alumno eliminado correctamente.');
        }

        return $this->irA('alumnos');
    }
}
