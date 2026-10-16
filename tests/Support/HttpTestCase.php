<?php

namespace Tests\Support;

use DOMDocument;
use DOMXPath;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\ConnectException;
use PDO;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

/**
 * Pruebas "caja negra": hacen peticiones HTTP reales al servidor de pruebas
 * (php -S ... con APP_ENV=testing) y revisan respuesta + base de datos.
 * Así funcionan sin depender de los detalles internos de Phalcon.
 */
abstract class HttpTestCase extends TestCase
{
    protected const PNG_1X1 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

    protected Client $http;
    protected PDO $pdo;
    protected array $config;
    protected string $raiz;
    private array $temporales = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->raiz = dirname(__DIR__, 2);
        $this->config = require $this->raiz . '/app/config/config.php';
        $db = $this->config['db'];

        // Protección: jamás tocar una base que no sea de pruebas
        if (!str_ends_with($db['dbname'], '_test')) {
            throw new \RuntimeException("La base '{$db['dbname']}' no termina en _test. Abortando para no borrar datos.");
        }

        $this->pdo = new PDO(
            "mysql:host={$db['host']};port={$db['port']};dbname={$db['dbname']};charset=utf8mb4",
            $db['username'],
            $db['password'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        $this->pdo->exec(file_get_contents($this->raiz . '/database/schema.sql'));

        // Limpiar archivos subidos en pruebas previas para aislamiento total
        foreach (glob($this->raiz . '/public/uploads/*') ?: [] as $archivoUpload) {
            if (!str_starts_with(basename($archivoUpload), '.')) {
                @unlink($archivoUpload);
            }
        }

        $this->http = new Client([
            'base_uri'        => getenv('TEST_BASE_URL') ?: 'http://127.0.0.1:8081',
            'cookies'         => new CookieJar(),
            'http_errors'     => false,
            'allow_redirects' => false,
            'timeout'         => 15,
        ]);

        try {
            (new Client(['base_uri' => getenv('TEST_BASE_URL') ?: 'http://127.0.0.1:8081', 'timeout' => 15, 'http_errors' => false]))->get('/');
        } catch (ConnectException $e) {
            $this->fail('El servidor de pruebas no responde. Inícialo (ver docs/07-PRUEBAS.md): '
                . 'APP_ENV=testing php -S 127.0.0.1:8081 -t public public/router.php');
        }

        // Crear usuario director por defecto e iniciar sesión en el cliente principal
        $this->insertar('usuarios', [
            'usuario'            => 'director',
            'password_hash'      => password_hash('Director123!', PASSWORD_DEFAULT),
            'rol'                => 'director',
            'activo'             => 1,
            'debe_cambiar_clave' => 0,
        ]);
        $this->loginComo('director');
    }

    protected function tearDown(): void
    {
        // borra fotos subidas por las pruebas
        foreach ($this->pdo->query('SELECT foto FROM alumnos WHERE foto IS NOT NULL')->fetchAll(PDO::FETCH_COLUMN) as $foto) {
            $ruta = $this->raiz . '/public/' . $foto;
            if (is_file($ruta)) {
                @unlink($ruta);
            }
        }
        foreach ($this->temporales as $t) {
            @unlink($t);
        }
        parent::tearDown();
    }

    // ---------- Datos de prueba directos en la BD ----------

    protected function insertar(string $tabla, array $fila): int
    {
        $cols = implode(',', array_map(fn($c) => "`$c`", array_keys($fila)));
        $marcas = implode(',', array_fill(0, count($fila), '?'));
        $this->pdo->prepare("INSERT INTO `$tabla` ($cols) VALUES ($marcas)")->execute(array_values($fila));
        return (int) $this->pdo->lastInsertId();
    }

    protected function contar(string $tabla): int
    {
        return (int) $this->pdo->query("SELECT COUNT(*) FROM `$tabla`")->fetchColumn();
    }

    protected function assertDatabaseHas(string $tabla, array $criterios): void
    {
        $where = [];
        $vals = [];
        foreach ($criterios as $col => $val) {
            $where[] = "`$col` = ?";
            $vals[] = $val;
        }
        $sql = "SELECT COUNT(*) FROM `$tabla` WHERE " . implode(' AND ', $where);
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($vals);
        $count = (int) $stmt->fetchColumn();
        $this->assertGreaterThan(0, $count, "No se encontró ningún registro en '$tabla' que coincida con los criterios: " . json_encode($criterios));
    }

    protected function crearAlumno(string $nombres, string $apellidos = 'Prueba', ?int $carreraId = null): int
    {
        $carreraId ??= $this->insertar('carreras', ['nombre' => 'Carrera ' . bin2hex(random_bytes(3))]);
        return $this->insertar('alumnos', [
            'nombres' => $nombres, 'apellidos' => $apellidos,
            'fecha_nacimiento' => '2008-01-01', 'carrera_id' => $carreraId,
        ]);
    }

    // ---------- HTTP ----------

    protected function get(string $ruta, array $query = []): ResponseInterface
    {
        return $this->http->get($ruta, ['query' => $query]);
    }

    protected function cuerpo(ResponseInterface $r): string
    {
        return (string) $r->getBody();
    }

    /**
     * Abre la página del formulario, toma sus campos ocultos (token CSRF incluido),
     * agrega los campos/archivos indicados y envía el formulario como lo haría un navegador.
     *
     * @param array $archivos ['campo' => ['ruta' => ..., 'nombre' => ..., 'tipo' => ...]]
     */
    protected function submit(string $paginaFormulario, array $campos, array $archivos = [], ?string $accion = null, ?Client $cliente = null): ResponseInterface
    {
        $cliente ??= $this->http;
        $pagina = $cliente->get($paginaFormulario);
        $this->assertSame(200, $pagina->getStatusCode(), "No se pudo abrir el formulario {$paginaFormulario}");

        $dom = new DOMDocument();
        @$dom->loadHTML('<?xml encoding="UTF-8">' . $this->cuerpo($pagina));
        $xp = new DOMXPath($dom);

        $forms = $xp->query("//main//form[translate(@method,'POST','post')='post']");
        if ($forms->length === 0) {
            $forms = $xp->query("//form[translate(@method,'POST','post')='post']");
        }

        $form = null;
        foreach ($forms as $f) {
            if ($accion === null || str_ends_with($f->getAttribute('action'), $accion)) {
                $form = $f;
                break;
            }
        }
        $this->assertNotNull($form, "No hay un formulario POST en {$paginaFormulario}");

        $ocultos = [];
        foreach ($xp->query('.//input[@type="hidden"]', $form) as $i) {
            $ocultos[$i->getAttribute('name')] = $i->getAttribute('value');
        }

        $partes = [];
        foreach (array_merge($ocultos, $campos) as $nombre => $valor) {
            $partes[] = ['name' => $nombre, 'contents' => (string) $valor];
        }
        foreach ($archivos as $nombre => $a) {
            $partes[] = [
                'name'     => $nombre,
                'contents' => fopen($a['ruta'], 'r'),
                'filename' => $a['nombre'],
                'headers'  => ['Content-Type' => $a['tipo']],
            ];
        }

        return $cliente->post($form->getAttribute('action') ?: $paginaFormulario, ['multipart' => $partes]);
    }

    /**
     * Inicia sesión con el cliente HTTP especificado.
     */
    protected function loguearCliente(Client $cliente, string $rol = 'director', array $extra = []): void
    {
        if ($rol === 'director') {
            $this->submit('/login', ['usuario' => 'director', 'password' => 'Director123!'], [], null, $cliente);
        } elseif ($rol === 'profesor') {
            $u = $extra['usuario'] ?? 'profesor1';
            $p = $extra['password'] ?? 'Profesor123!';
            $this->submit('/login', ['usuario' => $u, 'password' => $p], [], null, $cliente);
        } elseif ($rol === 'alumno') {
            $u = $extra['usuario'] ?? 'alumno1';
            $p = $extra['password'] ?? 'Alumno123!';
            $this->submit('/login', ['usuario' => $u, 'password' => $p], [], null, $cliente);
        }
    }

    /**
     * Configura la sesión actual de $this->http según el rol solicitado.
     */
    protected function loginComo(string $rol = 'director', array $extra = []): void
    {
        $this->http = new Client([
            'base_uri'        => getenv('TEST_BASE_URL') ?: 'http://127.0.0.1:8081',
            'cookies'         => new CookieJar(),
            'http_errors'     => false,
            'allow_redirects' => false,
            'timeout'         => 15,
        ]);

        if ($rol === 'invitado') {
            return;
        }

        if ($rol === 'profesor' && empty($extra['usuario'])) {
            $profesorId = $extra['profesor_id'] ?? null;
            if ($profesorId) {
                $existente = $this->pdo->query("SELECT usuario FROM usuarios WHERE profesor_id = " . (int) $profesorId)->fetchColumn();
                if ($existente) {
                    $extra['usuario'] = $existente;
                    $extra['password'] = 'Profesor123!';
                }
            }
            if (empty($extra['usuario'])) {
                $profesorId ??= $this->insertar('profesores', [
                    'nombres'   => 'Profesor',
                    'apellidos' => 'Prueba',
                    'email'     => 'prof_' . bin2hex(random_bytes(3)) . '@escuela.edu',
                ]);
                $u = 'prof_' . bin2hex(random_bytes(3));
                $p = 'Profesor123!';
                $this->insertar('usuarios', [
                    'usuario'            => $u,
                    'password_hash'      => password_hash($p, PASSWORD_DEFAULT),
                    'rol'                => 'profesor',
                    'profesor_id'        => $profesorId,
                    'activo'             => 1,
                    'debe_cambiar_clave' => 0,
                ]);
                $extra['usuario'] = $u;
                $extra['password'] = $p;
                $extra['profesor_id'] = $profesorId;
            }
        } elseif ($rol === 'alumno' && empty($extra['usuario'])) {
            $alumnoId = $extra['alumno_id'] ?? null;
            if ($alumnoId) {
                $existente = $this->pdo->query("SELECT usuario FROM usuarios WHERE alumno_id = " . (int) $alumnoId)->fetchColumn();
                if ($existente) {
                    $extra['usuario'] = $existente;
                    $extra['password'] = 'Alumno123!';
                }
            }
            if (empty($extra['usuario'])) {
                $alumnoId ??= $this->crearAlumno('Alumno', 'Prueba');
                $u = 'alum_' . bin2hex(random_bytes(3));
                $p = 'Alumno123!';
                $this->insertar('usuarios', [
                    'usuario'            => $u,
                    'password_hash'      => password_hash($p, PASSWORD_DEFAULT),
                    'rol'                => 'alumno',
                    'alumno_id'          => $alumnoId,
                    'activo'             => 1,
                    'debe_cambiar_clave' => 0,
                ]);
                $extra['usuario'] = $u;
                $extra['password'] = $p;
                $extra['alumno_id'] = $alumnoId;
            }
        }

        $this->loguearCliente($this->http, $rol, $extra);
    }

    // ---------- Archivos ----------

    protected function archivo(string $nombre, string $contenido, string $tipo): array
    {
        $ruta = tempnam(sys_get_temp_dir(), 'prueba_');
        file_put_contents($ruta, $contenido);
        $this->temporales[] = $ruta;
        return ['ruta' => $ruta, 'nombre' => $nombre, 'tipo' => $tipo];
    }

    protected function png(string $nombre = 'foto.png'): array
    {
        return $this->archivo($nombre, base64_decode(self::PNG_1X1), 'image/png');
    }
}
