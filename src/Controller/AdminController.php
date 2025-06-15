<?php


namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use MongoDB\Client;
use MongoDB\BSON\ObjectId;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'admin_panel')]
    public function dashboard(SessionInterface $session): Response
    {
        if ($session->get('rol') !== 'admin') {
            return $this->render('admin/acceso_denegado.html.twig');
        }

        $client = new Client("mongodb+srv://usuario1:arshak2003@proyectomongo.vfdni.mongodb.net/");
        $db = $client->vida_saludable;

        $totalUsuarios = $db->usuarios->countDocuments();
        $totalRutinas = $db->rutinas->countDocuments();
        $totalEjercicios = $db->ejercicios->countDocuments();
        $totalRegistrosCalendario = $db->calendario->countDocuments();

        $fechaInicioSemana = (new \DateTime())->modify('monday this week')->format('Y-m-d');
        $usuariosActivosSemana = $db->calendario->distinct('usuario_id', ['semana_inicio' => ['$gte' => $fechaInicioSemana]]);
        $usuariosActivosCount = count($usuariosActivosSemana);

        $fechaMesAtras = (new \DateTime('-1 month'))->format(\DateTime::ATOM);
        $usuariosNuevos = $db->usuarios->countDocuments([
            'fecha_creacion' => ['$gte' => $fechaMesAtras]
        ]);

        $totalEntrenamientos = 0;
        $usuariosUnicos = [];
        foreach ($db->calendario->find() as $registro) {
            $usuariosUnicos[$registro['usuario_id']] = true;
            if (isset($registro['dias']) && is_iterable($registro['dias'])) {
                foreach ($registro['dias'] as $dia) {
                    if (!empty($dia['entrenado'])) $totalEntrenamientos++;
                }
            }
        }
        $promedioEntrenamientos = $totalEntrenamientos > 0 ? round($totalEntrenamientos / max(count($usuariosUnicos), 1), 2) : 0;

        $fechaLimite = (new \DateTime())->modify('-14 days')->format('Y-m-d');
        $usuariosConActividad = $db->calendario->distinct('usuario_id', ['semana_inicio' => ['$gte' => $fechaLimite]]);
        $usuariosTotales = $db->usuarios->distinct('_id');
        $usuariosInactivos = array_diff($usuariosTotales, $usuariosConActividad);
        $usuariosInactivosCount = count($usuariosInactivos);

        $opinionesCursor = $db->opiniones->find([], ['sort' => ['fecha' => -1]]);
        $opiniones = iterator_to_array($opinionesCursor);
        $mediaOpiniones = count($opiniones) > 0 ? round(array_sum(array_column($opiniones, 'puntuacion')) / count($opiniones), 2) : 0;

        return $this->render('admin/panel.html.twig', [
            'totalUsuarios' => $totalUsuarios,
            'totalRutinas' => $totalRutinas,
            'totalEjercicios' => $totalEjercicios,
            'totalRegistrosCalendario' => $totalRegistrosCalendario,
            'usuariosActivosCount' => $usuariosActivosCount,
            'promedioEntrenamientos' => $promedioEntrenamientos,
            'mediaOpiniones' => $mediaOpiniones,
            'usuariosInactivosCount' => $usuariosInactivosCount,
            'opiniones' => $opiniones,
        ]);
    }
    #[Route('/admin/crear-ejercicio', name: 'admin_crear_ejercicio')]
    public function crearEjercicio(Request $request, SessionInterface $session): Response
    {
        if ($session->get('rol') !== 'admin') {
            return $this->render('admin/acceso_denegado.html.twig');
        }

        $mensaje = null;

        if ($request->isMethod('POST')) {
            $nombre = $request->request->get('nombre');
            $grupo = $request->request->get('grupo');
            $nivel = $request->request->get('nivel');
            $descripcion = $request->request->get('descripcion');
            $duracion = (int)$request->request->get('duracion');
            $repeticiones = $request->request->get('repeticiones');
            $video = $request->request->get('video');

            // Convertir URL de YouTube a embed
            $videoEmbed = $video;
            if (strpos($video, 'youtube.com/watch?v=') !== false) {
                parse_str(parse_url($video, PHP_URL_QUERY), $params);
                $videoEmbed = "https://www.youtube.com/embed/" . ($params['v'] ?? '');
            } elseif (strpos($video, 'youtu.be/') !== false) {
                $parts = explode('/', $video);
                $videoEmbed = "https://www.youtube.com/embed/" . end($parts);
            }

            $client = new \MongoDB\Client("mongodb+srv://usuario1:arshak2003@proyectomongo.vfdni.mongodb.net/");
            $db = $client->vida_saludable;
            $coleccion = $db->ejercicios;

            $ejercicio = [
                'nombre' => $nombre,
                'grupo_muscular' => $grupo,
                'nivel' => $nivel,
                'descripcion' => $descripcion,
                'duracion_aprox_min' => $duracion,
                'repeticiones' => $repeticiones,
                'video' => $videoEmbed
            ];

            try {
                $coleccion->insertOne($ejercicio);
                $mensaje = "Ejercicio insertado correctamente.";
            } catch (\Exception $e) {
                $mensaje = "Error: " . $e->getMessage();
            }
        }

        return $this->render('admin/crear_ejercicio.html.twig', [
            'mensaje' => $mensaje
        ]);
    }
    #[Route('/admin/crear-dieta', name: 'admin_crear_dieta')]
    public function crearDieta(Request $request, SessionInterface $session): Response
    {
        if ($session->get('rol') !== 'admin') {
            return $this->render('admin/acceso_denegado.html.twig');
        }

        $mensaje = null;

        if ($request->isMethod('POST')) {
            $nombre = $request->request->get('nombre');
            $alimentos = $request->request->get('alimentos');
            $calorias = (int)$request->request->get('calorias');
            $objetivo = $request->request->get('objetivo');
            $ejercicio = $request->request->get('ejercicio');

            $client = new \MongoDB\Client("mongodb+srv://usuario1:arshak2003@proyectomongo.vfdni.mongodb.net/");
            $db = $client->vida_saludable;

            $dieta = [
                'nombre' => $nombre,
                'alimentos' => $alimentos,
                'calorias' => $calorias,
                'objetivo' => $objetivo,
                'ejercicio' => $ejercicio,
                'fecha_creacion' => new \MongoDB\BSON\UTCDateTime()
            ];

            try {
                $db->dietas->insertOne($dieta);
                $mensaje = "Dieta añadida correctamente.";
            } catch (\Exception $e) {
                $mensaje = "Error: " . $e->getMessage();
            }
        }

        return $this->render('admin/crear_dieta.html.twig', [
            'mensaje' => $mensaje
        ]);
    }
    #[Route('/admin/crear-rutina', name: 'admin_crear_rutina')]
    public function crearRutina(Request $request, SessionInterface $session): Response
    {
        if ($session->get('rol') !== 'admin') {
            return $this->render('admin/acceso_denegado.html.twig');
        }

        $mensaje = null;
        $client = new \MongoDB\Client("mongodb+srv://usuario1:arshak2003@proyectomongo.vfdni.mongodb.net/");
        $db = $client->vida_saludable;
        $coleccionEjercicios = $db->ejercicios;

        // Obtener todos los ejercicios para mostrar en el formulario
        $ejercicios = iterator_to_array($coleccionEjercicios->find([], ['sort' => ['nombre' => 1]]));

        if ($request->isMethod('POST')) {
            $titulo = $request->request->get('titulo');
            $objetivo = $request->request->get('objetivo');
            $nivel = $request->request->get('nivel');
            $duracion = (int)$request->request->get('duracion');
            $ejerciciosSeleccionados = $request->request->all('ejercicios');

            $rutina = [
                'titulo' => $titulo,
                'objetivo' => $objetivo,
                'nivel' => $nivel,
                'duracion_total_min' => $duracion,
                'ejercicios' => $ejerciciosSeleccionados ?? []
            ];

            try {
                $db->rutinas->insertOne($rutina);
                $mensaje = "Rutina creada correctamente.";
            } catch (\Exception $e) {
                $mensaje = "Error: " . $e->getMessage();
            }
        }

        return $this->render('admin/crear_rutina.html.twig', [
            'mensaje' => $mensaje,
            'ejercicios' => $ejercicios
        ]);
    }
    #[Route('/admin/ver-ejercicios', name: 'admin_ver_ejercicios')]
    public function verEjercicios(SessionInterface $session): Response
    {
        if ($session->get('rol') !== 'admin') {
            return $this->render('admin/acceso_denegado.html.twig');
        }

        $client = new \MongoDB\Client("mongodb+srv://usuario1:arshak2003@proyectomongo.vfdni.mongodb.net/");
        $db = $client->vida_saludable;
        $ejercicios = iterator_to_array($db->ejercicios->find([], ['sort' => ['nombre' => 1]]));

        return $this->render('admin/ver_ejercicios.html.twig', [
            'ejercicios' => $ejercicios
        ]);
    }
    #[Route('/admin/ver-rutinas', name: 'admin_ver_rutinas')]
    public function verRutinas(SessionInterface $session): Response
    {
        if ($session->get('rol') !== 'admin') {
            return $this->render('admin/acceso_denegado.html.twig');
        }

        $client = new \MongoDB\Client("mongodb+srv://usuario1:arshak2003@proyectomongo.vfdni.mongodb.net/");
        $db = $client->vida_saludable;

        $rutinas = iterator_to_array($db->rutinas->find([], ['sort' => ['titulo' => 1]]));
        $ejerciciosCol = $db->ejercicios->find();
        $ejerciciosMap = [];
        foreach ($ejerciciosCol as $e) {
            $ejerciciosMap[(string)$e['_id']] = $e;
        }

        return $this->render('admin/ver_rutinas.html.twig', [
            'rutinas' => $rutinas,
            'ejerciciosMap' => $ejerciciosMap
        ]);
    }
    #[Route('/admin/ver-dietas', name: 'admin_ver_dietas')]
    public function verDietas(SessionInterface $session): Response
    {
        if ($session->get('rol') !== 'admin') {
            return $this->render('admin/acceso_denegado.html.twig');
        }

        $client = new \MongoDB\Client("mongodb+srv://usuario1:arshak2003@proyectomongo.vfdni.mongodb.net/");
        $db = $client->vida_saludable;
        $dietas = iterator_to_array(
            $db->dietas->find([], ['sort' => ['fecha_registro' => -1]])
        );

        $objetivoMap = [
            'perder_peso' => 'Perder peso',
            'mantener_peso' => 'Mantener peso',
            'ganar_musculo' => 'Ganar masa muscular'
        ];
        $ejercicioMap = [
            'sedentario' => 'Sedentario',
            'ligero' => 'Ligero',
            'moderado' => 'Moderado',
            'intenso' => 'Intenso'
        ];

        return $this->render('admin/ver_dietas.html.twig', [
            'dietas' => $dietas,
            'objetivoMap' => $objetivoMap,
            'ejercicioMap' => $ejercicioMap
        ]);
    }
    #[Route('/admin/eliminar-ejercicio/{id}', name: 'admin_eliminar_ejercicio', methods: ['POST'])]
    public function eliminarEjercicio($id, SessionInterface $session): Response
    {
        if ($session->get('rol') !== 'admin') {
            return $this->render('admin/acceso_denegado.html.twig');
        }
        $client = new \MongoDB\Client("mongodb+srv://usuario1:arshak2003@proyectomongo.vfdni.mongodb.net/");
        $db = $client->vida_saludable;
        $db->ejercicios->deleteOne(['_id' => new \MongoDB\BSON\ObjectId($id)]);
        return $this->redirectToRoute('admin_ver_ejercicios');
    }
    #[Route('/admin/eliminar-rutina/{id}', name: 'admin_eliminar_rutina', methods: ['POST'])]
    public function eliminarRutina($id, SessionInterface $session): Response
    {
        if ($session->get('rol') !== 'admin') {
            return $this->render('admin/acceso_denegado.html.twig');
        }
        $client = new \MongoDB\Client("mongodb+srv://usuario1:arshak2003@proyectomongo.vfdni.mongodb.net/");
        $db = $client->vida_saludable;
        $db->rutinas->deleteOne(['_id' => new \MongoDB\BSON\ObjectId($id)]);
        return $this->redirectToRoute('admin_ver_rutinas');
    }

    #[Route('/admin/eliminar-dieta/{id}', name: 'admin_eliminar_dieta', methods: ['POST'])]
    public function eliminarDieta($id, SessionInterface $session): Response
    {
        if ($session->get('rol') !== 'admin') {
            return $this->render('admin/acceso_denegado.html.twig');
        }
        $client = new \MongoDB\Client("mongodb+srv://usuario1:arshak2003@proyectomongo.vfdni.mongodb.net/");
        $db = $client->vida_saludable;
        $db->dietas->deleteOne(['_id' => new \MongoDB\BSON\ObjectId($id)]);
        return $this->redirectToRoute('admin_ver_dietas');
    }
    #[Route('/admin/editar-ejercicio/{id}', name: 'admin_editar_ejercicio')]
    public function editarEjercicio($id, Request $request, SessionInterface $session): Response
    {
        if ($session->get('rol') !== 'admin') {
            return $this->render('admin/acceso_denegado.html.twig');
        }
        $client = new \MongoDB\Client("mongodb+srv://usuario1:arshak2003@proyectomongo.vfdni.mongodb.net/");
        $db = $client->vida_saludable;
        $coleccion = $db->ejercicios;
        $ejercicio = $coleccion->findOne(['_id' => new \MongoDB\BSON\ObjectId($id)]);
        $mensaje = null;

        if ($request->isMethod('POST')) {
            $update = [
                'nombre' => $request->request->get('nombre'),
                'grupo_muscular' => $request->request->get('grupo'),
                'nivel' => $request->request->get('nivel'),
                'descripcion' => $request->request->get('descripcion'),
                'duracion_aprox_min' => (int)$request->request->get('duracion'),
                'repeticiones' => $request->request->get('repeticiones'),
                'video' => $request->request->get('video')
            ];
            $coleccion->updateOne(['_id' => new \MongoDB\BSON\ObjectId($id)], ['$set' => $update]);
            $mensaje = "Ejercicio actualizado correctamente.";
            // Recargar datos actualizados
            $ejercicio = $coleccion->findOne(['_id' => new \MongoDB\BSON\ObjectId($id)]);
        }

        return $this->render('admin/editar_ejercicio.html.twig', [
            'ejercicio' => $ejercicio,
            'mensaje' => $mensaje
        ]);
    }

    #[Route('/admin/editar-rutina/{id}', name: 'admin_editar_rutina')]
    public function editarRutina($id, Request $request, SessionInterface $session): Response
    {
        if ($session->get('rol') !== 'admin') {
            return $this->render('admin/acceso_denegado.html.twig');
        }
        $client = new \MongoDB\Client("mongodb+srv://usuario1:arshak2003@proyectomongo.vfdni.mongodb.net/");
        $db = $client->vida_saludable;
        $coleccionRutinas = $db->rutinas;
        $coleccionEjercicios = $db->ejercicios;
        $rutina = $coleccionRutinas->findOne(['_id' => new \MongoDB\BSON\ObjectId($id)]);
        $ejercicios = iterator_to_array($coleccionEjercicios->find([], ['sort' => ['nombre' => 1]]));
        $mensaje = null;

        if ($request->isMethod('POST')) {
            $update = [
                'titulo' => $request->request->get('titulo'),
                'objetivo' => $request->request->get('objetivo'),
                'nivel' => $request->request->get('nivel'),
                'duracion_total_min' => (int)$request->request->get('duracion'),
                'ejercicios' => $request->request->all('ejercicios') ?? []
            ];
            $coleccionRutinas->updateOne(['_id' => new \MongoDB\BSON\ObjectId($id)], ['$set' => $update]);
            $mensaje = "Rutina actualizada correctamente.";
            // Recargar datos actualizados
            $rutina = $coleccionRutinas->findOne(['_id' => new \MongoDB\BSON\ObjectId($id)]);
        }

        return $this->render('admin/editar_rutina.html.twig', [
            'rutina' => $rutina,
            'ejercicios' => $ejercicios,
            'mensaje' => $mensaje
        ]);
    }

    #[Route('/admin/editar-dieta/{id}', name: 'admin_editar_dieta')]
    public function editarDieta($id, Request $request, SessionInterface $session): Response
    {
        if ($session->get('rol') !== 'admin') {
            return $this->render('admin/acceso_denegado.html.twig');
        }
        $client = new \MongoDB\Client("mongodb+srv://usuario1:arshak2003@proyectomongo.vfdni.mongodb.net/");
        $db = $client->vida_saludable;
        $coleccion = $db->dietas;
        $dieta = $coleccion->findOne(['_id' => new \MongoDB\BSON\ObjectId($id)]);
        $mensaje = null;

        // Convertir alimentos a texto para el textarea (antes del POST)
        if ($dieta && isset($dieta['alimentos'])) {
            if ($dieta['alimentos'] instanceof \MongoDB\Model\BSONArray || is_array($dieta['alimentos'])) {
                $alimentosArray = (array)$dieta['alimentos'];
                $lines = [];
                foreach ($alimentosArray as $alimento) {
                    if (is_string($alimento)) {
                        $lines[] = $alimento;
                    } elseif (is_object($alimento) && isset($alimento->nombre)) {
                        $lines[] = $alimento->nombre;
                    } elseif (is_array($alimento) && isset($alimento['nombre'])) {
                        $lines[] = $alimento['nombre'];
                    } elseif ($alimento instanceof \MongoDB\Model\BSONDocument && isset($alimento['nombre'])) {
                        $lines[] = $alimento['nombre'];
                    } elseif (is_object($alimento) && method_exists($alimento, 'getArrayCopy')) {
                        $arr = $alimento->getArrayCopy();
                        $lines[] = $arr['nombre'] ?? json_encode($arr);
                    } else {
                        $lines[] = json_encode($alimento);
                    }
                }
                $dieta['alimentos_texto'] = implode("\n", $lines);
            } else {
                $dieta['alimentos_texto'] = (string)$dieta['alimentos'];
            }
        } else {
            $dieta['alimentos_texto'] = '';
        }

        if ($request->isMethod('POST')) {
            // Convertir el textarea a array (una línea por alimento)
            $alimentosInput = $request->request->get('alimentos');
            $alimentosArray = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $alimentosInput)));

            $update = [
                'nombre' => $request->request->get('nombre'),
                'alimentos' => $alimentosArray,
                'calorias' => (int)$request->request->get('calorias'),
                'objetivo' => $request->request->get('objetivo'),
                'ejercicio' => $request->request->get('ejercicio')
            ];
            $coleccion->updateOne(['_id' => new \MongoDB\BSON\ObjectId($id)], ['$set' => $update]);
            $mensaje = "Dieta actualizada correctamente.";
            // Recargar datos actualizados
            $dieta = $coleccion->findOne(['_id' => new \MongoDB\BSON\ObjectId($id)]);
            // Volver a preparar el campo para el textarea (después del POST)
            if ($dieta && isset($dieta['alimentos'])) {
                if ($dieta['alimentos'] instanceof \MongoDB\Model\BSONArray || is_array($dieta['alimentos'])) {
                    $alimentosArray = (array)$dieta['alimentos'];
                    $lines = [];
                    foreach ($alimentosArray as $alimento) {
                        if (is_string($alimento)) {
                            $lines[] = $alimento;
                        } elseif (is_object($alimento) && isset($alimento->nombre)) {
                            $lines[] = $alimento->nombre;
                        } elseif (is_array($alimento) && isset($alimento['nombre'])) {
                            $lines[] = $alimento['nombre'];
                        } elseif ($alimento instanceof \MongoDB\Model\BSONDocument && isset($alimento['nombre'])) {
                            $lines[] = $alimento['nombre'];
                        } elseif (is_object($alimento) && method_exists($alimento, 'getArrayCopy')) {
                            $arr = $alimento->getArrayCopy();
                            $lines[] = $arr['nombre'] ?? json_encode($arr);
                        } else {
                            $lines[] = json_encode($alimento);
                        }
                    }
                    $dieta['alimentos_texto'] = implode("\n", $lines);
                } else {
                    $dieta['alimentos_texto'] = (string)$dieta['alimentos'];
                }
            } else {
                $dieta['alimentos_texto'] = '';
            }
        }

        return $this->render('admin/editar_dieta.html.twig', [
            'dieta' => $dieta,
            'mensaje' => $mensaje
        ]);
    }
}
