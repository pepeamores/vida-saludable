<?php


namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use MongoDB\Client;
use MongoDB\BSON\ObjectId;

class AlimentacionController extends AbstractController
{
    #[Route('/alimentacion', name: 'alimentacion')]
    public function index(Request $request, SessionInterface $session): Response
    {
        // Conexión a MongoDB
        $client = new Client("mongodb+srv://usuario1:arshak2003@proyectomongo.vfdni.mongodb.net/");
        $db = $client->vida_saludable;
        $usersCollection = $db->usuarios;
        $dietasCollection = $db->dietas;

        // Inicialización de variables
        $tmb = null;
        $dietasRecomendadas = null;
        $userData = null;
        $edadCalculada = '';
        $pesoForm = '';
        $alturaForm = '';
        $sexoForm = '';
        $edadForm = '';
        $objetivoForm = '';
        $ejercicioForm = '';

        // Cargar datos del usuario si está logueado
        if ($session->has('user_id')) {
            $userId = $session->get('user_id');
            try {
                $userData = $usersCollection->findOne(['_id' => new ObjectId($userId)]);
                if ($userData) {
                    $pesoForm = $userData['peso'] ?? '';
                    $alturaForm = $userData['altura'] ?? '';
                    $sexoForm = $userData['sexo'] ?? '';
                    if (isset($userData['fecha_nacimiento']) && !empty($userData['fecha_nacimiento'])) {
                        $edadCalculada = $this->calcularEdadDesdeCadena($userData['fecha_nacimiento']);
                        $edadForm = $edadCalculada;
                    } else {
                        $edadCalculada = $session->get('fecha_nacimiento') ? $this->calcularEdadDesdeCadena($session->get('fecha_nacimiento')) : '';
                        $edadForm = $edadCalculada;
                    }
                }
            } catch (\Exception $e) {
                $this->addFlash('error', 'Hubo un problema al cargar tus datos de usuario.');
                $session->remove('user_id');
            }
        }

        // Procesar el formulario si se ha enviado (POST)
        if ($request->isMethod('POST')) {
            $peso = (float) ($request->request->get('peso') ?? 0);
            $altura = (float) ($request->request->get('altura') ?? 0);
            $sexo = $request->request->get('sexo') ?? '';
            $edad = (int) ($request->request->get('edad') ?? 0);
            $objetivo = $request->request->get('objetivo') ?? '';
            $ejercicio = $request->request->get('ejercicio') ?? '';

            // Guardar en sesión (opcional)
            $_SESSION['peso'] = $peso;
            $_SESSION['altura'] = $altura;
            $_SESSION['sexo'] = $sexo;

            // Si el usuario está logueado, actualiza sus datos en la base de datos
            if ($session->has('user_id')) {
                $userId = $session->get('user_id');
                try {
                    $usersCollection->updateOne(
                        ['_id' => new ObjectId($userId)],
                        ['$set' => [
                            'peso' => $peso,
                            'altura' => $altura,
                            'sexo' => $sexo
                        ]]
                    );
                    // Recarga los datos del usuario después de actualizar
                    $userData = $usersCollection->findOne(['_id' => new ObjectId($userId)]);
                    $pesoForm = $userData['peso'] ?? '';
                    $alturaForm = $userData['altura'] ?? '';
                    $sexoForm = $userData['sexo'] ?? '';
                    if (isset($userData['fecha_nacimiento']) && !empty($userData['fecha_nacimiento'])) {
                        $edadCalculada = $this->calcularEdadDesdeCadena($userData['fecha_nacimiento']);
                        $edadForm = $edadCalculada;
                    } else {
                        $edadCalculada = $session->get('fecha_nacimiento') ? $this->calcularEdadDesdeCadena($session->get('fecha_nacimiento')) : '';
                        $edadForm = $edadCalculada;
                    }
                } catch (\Exception $e) {
                    $this->addFlash('error', 'No se pudieron actualizar tus datos.');
                }
            } else {
                // Si no está logueado, usa los datos del POST
                $pesoForm = $peso;
                $alturaForm = $altura;
                $sexoForm = $sexo;
                $edadForm = $edad;
            }

            $objetivoForm = $objetivo;
            $ejercicioForm = $ejercicio;

            // Harris-Benedict Formula
            $tmbCalculado = 0;
            if ($sexo === 'masculino') {
                $tmbCalculado = 88.36 + (13.4 * $peso) + (4.8 * $altura) - (5.7 * $edad);
            } elseif ($sexo === 'femenino') {
                $tmbCalculado = 447.6 + (9.2 * $peso) + (3.1 * $altura) - (4.3 * $edad);
            }

            // Factores de actividad
            $factor = 1.2;
            if ($ejercicio === 'sedentario') $factor = 1.2;
            if ($ejercicio === 'ligero') $factor = 1.375;
            if ($ejercicio === 'moderado') $factor = 1.55;
            if ($ejercicio === 'intenso') $factor = 1.725;

            $calorias = $tmbCalculado * $factor;

            // Ajuste según objetivo
            if ($objetivo === 'perder_peso') {
                $calorias -= 500;
            } elseif ($objetivo === 'ganar_musculo') {
                $calorias += 300;
            }
            $tmb = $calorias;

            // Buscar dietas recomendadas
            $dietasMongoCursor = $dietasCollection->find([
                'objetivo' => $objetivo,
                'ejercicio' => $ejercicio
            ]);
            $dietasRecomendadasArray = iterator_to_array($dietasMongoCursor);
            usort($dietasRecomendadasArray, function ($a, $b) use ($tmb) {
                $caloriasA = $a['calorias'] ?? 0;
                $caloriasB = $b['calorias'] ?? 0;
                return abs($caloriasA - $tmb) <=> abs($caloriasB - $tmb);
            });
            $dietasRecomendadas = $dietasRecomendadasArray;
        }

        // Prepara el array datos para la vista
            $datos = [
                'nombre'    => $userData['nombre']    ?? '',
                'apellidos' => $userData['apellidos'] ?? '',
                'edad'      => $edadCalculada         ?? '',
                'altura'    => $alturaForm            ?? '',
                'peso'      => $pesoForm              ?? '',
                'sexo'      => $sexoForm              ?? '',
                'email'     => $userData['email']     ?? '',
                'usuario'   => $userData['usuario']   ?? '',
                'user_id'   => $session->get('user_id') ?? '',
            ];

        // Renderizar la vista, pasando todas las variables necesarias
            return $this->render('main/alimentacion.html.twig', [
                'tmb' => $tmb,
                'dietasRecomendadas' => $dietasRecomendadas,
                'datos' => $datos,
                'objetivo' => $objetivoForm ?? '',
                'ejercicio' => $ejercicioForm ?? '',
                'edadCalculada' => $edadCalculada ?? '',
                'edad' => $edadForm ?? '',
                'isPostRequest' => $request->isMethod('POST'),
            ]);
    }

    /**
     * Calcula la edad a partir de una cadena de fecha de nacimiento.
     */
    private function calcularEdadDesdeCadena(?string $fechaNacimiento): int|string
    {
        if (!$fechaNacimiento) {
            return '';
        }
        try {
            $fecha = new \DateTime($fechaNacimiento);
            $hoy = new \DateTime();
            return $hoy->diff($fecha)->y;
        } catch (\Exception $e) {
            return '';
        }
    }
}