<?php

namespace App\Controllers;

use App\Models\Alumno;
use App\Models\Carrera;
use App\Models\Curso;
use App\Models\Nota;
use App\Models\Seccion;
use App\Models\Semestre;
use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\Validator\PresenceOf;
use Phalcon\Filter\Validation\Validator\StringLength;

/**
 * Controlador centralizado para los 4 catálogos escolares:
 * carreras, cursos, secciones y semestres.
 */
class CatalogosController extends BaseController
{
    private const CATALOGOS = [
        'carreras' => [
            'modelo'           => Carrera::class,
            'singular'         => 'Carrera',
            'plural'           => 'Carreras',
            'articulo'         => 'la',
            'descripcion'      => 'Gestión de carreras de bachillerato disponibles',
            'icono'            => 'bi bi-award text-primary',
            'campo'            => 'Nombre de la Carrera',
            'placeholder'      => 'Ej. Bachillerato en Computación',
            'maxLength'        => 100,
            'etiquetaRelacion' => 'Alumnos Inscritos',
            'relacionTexto'    => 'alumno(s)',
        ],
        'cursos' => [
            'modelo'           => Curso::class,
            'singular'         => 'Curso',
            'plural'           => 'Cursos',
            'articulo'         => 'el',
            'descripcion'      => 'Gestión de cursos o asignaturas académicas',
            'icono'            => 'bi bi-book text-success',
            'campo'            => 'Nombre del Curso',
            'placeholder'      => 'Ej. Matemática I',
            'maxLength'        => 100,
            'etiquetaRelacion' => 'Notas Registradas',
            'relacionTexto'    => 'nota(s)',
        ],
        'secciones' => [
            'modelo'           => Seccion::class,
            'singular'         => 'Sección',
            'plural'           => 'Secciones',
            'articulo'         => 'la',
            'descripcion'      => 'Gestión de secciones académicas (ej. A, B, C)',
            'icono'            => 'bi bi-grid-3x3-gap text-warning',
            'campo'            => 'Nombre de la Sección',
            'placeholder'      => 'Ej. A',
            'maxLength'        => 50,
            'etiquetaRelacion' => 'Notas Registradas',
            'relacionTexto'    => 'nota(s)',
        ],
        'semestres' => [
            'modelo'           => Semestre::class,
            'singular'         => 'Semestre',
            'plural'           => 'Semestres',
            'articulo'         => 'el',
            'descripcion'      => 'Gestión de ciclos o periodos académicos',
            'icono'            => 'bi bi-calendar3 text-info',
            'campo'            => 'Nombre del Semestre',
            'placeholder'      => 'Ej. Primer Semestre 2026',
            'maxLength'        => 100,
            'etiquetaRelacion' => 'Notas Registradas',
            'relacionTexto'    => 'nota(s)',
        ],
    ];

    private function getTipo(): string
    {
        $tipo = (string) $this->dispatcher->getParam('tipo');
        if (!isset(self::CATALOGOS[$tipo])) {
            throw new \RuntimeException('Catálogo no reconocido');
        }
        return $tipo;
    }

    private function getMeta(string $tipo): array
    {
        return self::CATALOGOS[$tipo];
    }

    public function indexAction(): void
    {
        $tipo = $this->getTipo();
        $meta = $this->getMeta($tipo);
        $clase = $meta['modelo'];

        $registros = $clase::find([
            'order' => 'nombre ASC',
        ]);

        $items = [];
        foreach ($registros as $reg) {
            $conteo = match ($tipo) {
                'carreras'  => Alumno::count(['conditions' => 'carrera_id = :id:', 'bind' => ['id' => (int) $reg->id]]),
                'cursos'    => Nota::count(['conditions' => 'curso_id = :id:', 'bind' => ['id' => (int) $reg->id]]),
                'secciones' => Nota::count(['conditions' => 'seccion_id = :id:', 'bind' => ['id' => (int) $reg->id]]),
                'semestres' => Nota::count(['conditions' => 'semestre_id = :id:', 'bind' => ['id' => (int) $reg->id]]),
                default     => 0,
            };

            $items[] = [
                'id'      => (int) $reg->id,
                'nombre'  => (string) $reg->nombre,
                'conteo'  => (int) $conteo,
                'cerrado' => $tipo === 'semestres' ? (bool) $reg->cerrado : false,
            ];
        }

        $this->view->setVar('tipo', $tipo);
        $this->view->setVar('meta', $meta);
        $this->view->setVar('items', $items);
        $this->view->pick('catalogos/index');
    }

    public function nuevoAction(): void
    {
        $tipo = $this->getTipo();
        $meta = $this->getMeta($tipo);

        $this->view->setVar('tipo', $tipo);
        $this->view->setVar('meta', $meta);
        $this->view->setVar('old', []);
        $this->view->setVar('errores', []);
        $this->view->pick('catalogos/nuevo');
    }

    public function crearAction()
    {
        $tipo = $this->getTipo();
        $meta = $this->getMeta($tipo);
        $clase = $meta['modelo'];

        $rawNombre = $this->request->getPost('nombre');
        $errores = [];
        if (!is_string($rawNombre)) {
            $errores[] = 'El nombre debe ser una cadena de texto';
            $nombre = '';
        } else {
            $nombre = trim($rawNombre);
        }

        $datos = [
            'nombre' => $nombre,
        ];

        $v = new Validation();
        $v->add('nombre', new PresenceOf([
            'message' => 'El nombre es obligatorio',
        ]));
        $v->add('nombre', new StringLength([
            'max'            => $meta['maxLength'],
            'messageMaximum' => "Máximo {$meta['maxLength']} caracteres",
        ]));

        foreach ($v->validate($datos) as $mensaje) {
            $errores[] = $mensaje->getMessage();
        }

        if (empty($errores) && $datos['nombre'] !== '') {
            $existe = $clase::findFirst([
                'conditions' => 'nombre = :nombre:',
                'bind'       => ['nombre' => $datos['nombre']],
            ]);
            if ($existe) {
                $errores[] = "Ya existe un registro en {$meta['plural']} con el nombre indicado";
            }
        }

        if (!empty($errores)) {
            $this->conErrores($errores, $datos);
            $this->view->setVar('tipo', $tipo);
            $this->view->setVar('meta', $meta);
            $this->view->pick('catalogos/nuevo');
            return;
        }

        $modelo = new $clase();
        $modelo->assign($datos, ['nombre']); // lista blanca segura

        if (!$modelo->save()) {
            $mensajes = array_map(fn($m) => $m->getMessage(), iterator_to_array($modelo->getMessages()));
            $this->conErrores($mensajes, $datos);
            $this->view->setVar('tipo', $tipo);
            $this->view->setVar('meta', $meta);
            $this->view->pick('catalogos/nuevo');
            return;
        }

        $this->flashSession->success("{$meta['singular']} creada correctamente.");
        return $this->irA("catalogos/{$tipo}");
    }

    public function editarAction(): void
    {
        $tipo = $this->getTipo();
        $meta = $this->getMeta($tipo);
        $clase = $meta['modelo'];
        $id = (int) $this->dispatcher->getParam('id');

        $item = $clase::findFirst([
            'conditions' => 'id = :id:',
            'bind'       => ['id' => $id],
        ]);

        if (!$item) {
            $this->dispatcher->forward(['controller' => 'error', 'action' => 'notFound']);
            return;
        }

        $this->view->setVar('tipo', $tipo);
        $this->view->setVar('meta', $meta);
        $this->view->setVar('item', $item);
        $this->view->setVar('old', ['nombre' => $item->nombre]);
        $this->view->setVar('errores', []);
        $this->view->pick('catalogos/editar');
    }

    public function actualizarAction()
    {
        $tipo = $this->getTipo();
        $meta = $this->getMeta($tipo);
        $clase = $meta['modelo'];
        $id = (int) $this->dispatcher->getParam('id');

        $item = $clase::findFirst([
            'conditions' => 'id = :id:',
            'bind'       => ['id' => $id],
        ]);

        if (!$item) {
            $this->dispatcher->forward(['controller' => 'error', 'action' => 'notFound']);
            return;
        }

        $rawNombre = $this->request->getPost('nombre');
        $errores = [];
        if (!is_string($rawNombre)) {
            $errores[] = 'El nombre debe ser una cadena de texto';
            $nombre = '';
        } else {
            $nombre = trim($rawNombre);
        }

        $datos = [
            'nombre' => $nombre,
        ];

        $v = new Validation();
        $v->add('nombre', new PresenceOf([
            'message' => 'El nombre es obligatorio',
        ]));
        $v->add('nombre', new StringLength([
            'max'            => $meta['maxLength'],
            'messageMaximum' => "Máximo {$meta['maxLength']} caracteres",
        ]));

        foreach ($v->validate($datos) as $mensaje) {
            $errores[] = $mensaje->getMessage();
        }

        if (empty($errores) && $datos['nombre'] !== '') {
            $existe = $clase::findFirst([
                'conditions' => 'nombre = :nombre: AND id != :id:',
                'bind'       => ['nombre' => $datos['nombre'], 'id' => $id],
            ]);
            if ($existe) {
                $errores[] = "Ya existe otro registro en {$meta['plural']} con el nombre indicado";
            }
        }

        if (!empty($errores)) {
            $this->conErrores($errores, $datos);
            $this->view->setVar('tipo', $tipo);
            $this->view->setVar('meta', $meta);
            $this->view->setVar('item', $item);
            $this->view->pick('catalogos/editar');
            return;
        }

        $item->assign($datos, ['nombre']); // lista blanca segura

        if (!$item->save()) {
            $mensajes = array_map(fn($m) => $m->getMessage(), iterator_to_array($item->getMessages()));
            $this->conErrores($mensajes, $datos);
            $this->view->setVar('tipo', $tipo);
            $this->view->setVar('meta', $meta);
            $this->view->setVar('item', $item);
            $this->view->pick('catalogos/editar');
            return;
        }

        $this->flashSession->success("{$meta['singular']} actualizada correctamente.");
        return $this->irA("catalogos/{$tipo}");
    }

    public function eliminarAction()
    {
        $tipo = $this->getTipo();
        $meta = $this->getMeta($tipo);
        $clase = $meta['modelo'];
        $id = (int) $this->dispatcher->getParam('id');

        $item = $clase::findFirst([
            'conditions' => 'id = :id:',
            'bind'       => ['id' => $id],
        ]);

        if (!$item) {
            $this->dispatcher->forward(['controller' => 'error', 'action' => 'notFound']);
            return;
        }

        // Verificación de integridad referencial
        $enUso = match ($tipo) {
            'carreras'  => Alumno::count(['conditions' => 'carrera_id = :id:', 'bind' => ['id' => $id]]) > 0,
            'cursos'    => Nota::count(['conditions' => 'curso_id = :id:', 'bind' => ['id' => $id]]) > 0,
            'secciones' => Nota::count(['conditions' => 'seccion_id = :id:', 'bind' => ['id' => $id]]) > 0,
            'semestres' => Nota::count(['conditions' => 'semestre_id = :id:', 'bind' => ['id' => $id]]) > 0,
            default     => false,
        };

        $singularMin = mb_strtolower((string) $meta['singular'], 'UTF-8');

        if ($enUso) {
            $this->flashSession->error("No se puede eliminar {$meta['articulo']} {$singularMin} porque tiene registros relacionados en uso.");
            return $this->irA("catalogos/{$tipo}");
        }

        try {
            if (!$item->delete()) {
                $this->flashSession->error("No se puede eliminar {$meta['articulo']} {$singularMin}: está en uso.");
            } else {
                $this->flashSession->success("{$meta['singular']} eliminada correctamente.");
            }
        } catch (\Throwable $e) {
            $this->flashSession->error("No se puede eliminar {$meta['articulo']} {$singularMin}: está en uso por otros datos.");
        }

        return $this->irA("catalogos/{$tipo}");
    }

    public function cerrarAction()
    {
        $tipo = $this->getTipo();
        if ($tipo !== 'semestres') {
            $this->dispatcher->forward(['controller' => 'error', 'action' => 'notFound']);
            return;
        }

        $id = (int) $this->dispatcher->getParam('id');
        /** @var Semestre|null $semestre */
        $semestre = Semestre::findFirst([
            'conditions' => 'id = :id:',
            'bind'       => ['id' => $id],
        ]);

        if (!$semestre) {
            $this->dispatcher->forward(['controller' => 'error', 'action' => 'notFound']);
            return;
        }

        $semestre->cerrado = 1;
        if ($semestre->save()) {
            $this->flashSession->success("El semestre '{$semestre->nombre}' ha sido cerrado. Solo la Dirección podrá modificar notas de este periodo.");
        } else {
            $this->flashSession->error("No se pudo cerrar el semestre.");
        }

        return $this->irA("catalogos/semestres");
    }

    public function reabrirAction()
    {
        $tipo = $this->getTipo();
        if ($tipo !== 'semestres') {
            $this->dispatcher->forward(['controller' => 'error', 'action' => 'notFound']);
            return;
        }

        $id = (int) $this->dispatcher->getParam('id');
        /** @var Semestre|null $semestre */
        $semestre = Semestre::findFirst([
            'conditions' => 'id = :id:',
            'bind'       => ['id' => $id],
        ]);

        if (!$semestre) {
            $this->dispatcher->forward(['controller' => 'error', 'action' => 'notFound']);
            return;
        }

        $semestre->cerrado = 0;
        if ($semestre->save()) {
            $this->flashSession->success("El semestre '{$semestre->nombre}' ha sido reabierto.");
        } else {
            $this->flashSession->error("No se pudo reabrir el semestre.");
        }

        return $this->irA("catalogos/semestres");
    }
}

