<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use MongoDB\Client;

class EjercicioController extends AbstractController
{
    #[Route('/ejercicio', name: 'ejercicio')]
    public function index(Request $request, SessionInterface $session): Response
    {
        $client = new Client("mongodb+srv://usuario1:arshak2003@proyectomongo.vfdni.mongodb.net/");
        $db = $client->vida_saludable;

        $objetivo = $request->query->get('objetivo');
        $nivel = $request->query->get('nivel');

        $rutinas = [];
        if ($objetivo) {
            $filtro = ['objetivo' => new \MongoDB\BSON\Regex('^' . preg_quote($objetivo) . '$', 'i')];
            if ($nivel) {
                $filtro['nivel'] = new \MongoDB\BSON\Regex('^' . preg_quote($nivel) . '$', 'i');
            }
            $rutinas = iterator_to_array($db->rutinas->find($filtro));
        }

        $ejercicios = iterator_to_array($db->ejercicios->find());
        $ejerciciosMap = [];
        foreach ($ejercicios as $ej) {
            $ejerciciosMap[(string) $ej['_id']] = $ej;
        }

        $graficas = [];
        if ($session->has('user_id')) {
            $graficas = $this->obtenerDatosGraficas($session->get('user_id'), $db);
        }

        return $this->render('main/ejercicio.html.twig', [
            'rutinas' => $rutinas,
            'ejercicios_map' => $ejerciciosMap,
            'datos' => $session->all(),
            'objetivo_actual' => $objetivo,
            'nivel_actual' => $nivel,
            'graficas' => $graficas
        ]);
    }

    #[Route('/guardar-calendario', name: 'guardar_calendario', methods: ['POST'])]
    public function guardarCalendario(Request $request, SessionInterface $session): Response
    {
        if (!$session->has('user_id')) {
            return $this->redirectToRoute('app_login');
        }

        $client = new Client("mongodb+srv://usuario1:arshak2003@proyectomongo.vfdni.mongodb.net/");
        $db = $client->vida_saludable;
        $coleccion = $db->calendario;

        $userId = $session->get('user_id');
        $entrenado = $request->request->all('entrenado');
        $notas = $request->request->all('notas');
        $dias = ['Lunes', 'Martes', 'Mi\u00e9rcoles', 'Jueves', 'Viernes', 'S\u00e1bado', 'Domingo'];

        $registro = [];
        foreach ($dias as $i => $dia) {
            $registro[$dia] = [
                'entrenado' => isset($entrenado[$i]) ? true : false,
                'nota' => $notas[$i] ?? ''
            ];
        }

        $coleccion->insertOne([
            'usuario_id' => $userId,
            'semana_inicio' => date('Y-m-d', strtotime('monday this week')),
            'semana_fin' => date('Y-m-d', strtotime('sunday this week')),
            'dias' => $registro
        ]);

        $this->addFlash('success', 'Tu calendario ha sido guardado correctamente.');
        return $this->redirectToRoute('ejercicio');
    }

    private function obtenerDatosGraficas($userId, $db): array
    {
        $calendario = $db->calendario->find(['usuario_id' => $userId]);

        $diasEntrenados = [];
        $frecuenciaPorDia = array_fill_keys(['lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado', 'domingo'], 0);
        $tiposEntrenamiento = [];

        foreach ($calendario as $semana) {
            $entrenadosSemana = 0;
            $dias = $semana['dias'] ?? [];

            foreach ($dias as $dia => $datos) {
                $dia = strtolower($dia);
                if (!empty($datos['entrenado'])) {
                    $entrenadosSemana++;

                    if (isset($frecuenciaPorDia[$dia])) {
                        $frecuenciaPorDia[$dia]++;
                    }

                    $nota = strtolower(trim($datos['notas'] ?? 'otro'));
                    $tipos = preg_split('/[,;]+/', $nota);

                    foreach ($tipos as $tipo) {
                        $tipo = trim($tipo);
                        if ($tipo !== '') {
                            $tiposEntrenamiento[$tipo] = ($tiposEntrenamiento[$tipo] ?? 0) + 1;
                        }
                    }
                }
            }

            $diasEntrenados[] = [
                'semana' => $semana['semana_inicio'] ?? 'Desconocida',
                'entrenamientos' => $entrenadosSemana
            ];
        }

        return [
            'diasEntrenados' => $diasEntrenados,
            'frecuenciaPorDia' => $frecuenciaPorDia,
            'tiposEntrenamiento' => $tiposEntrenamiento
        ];
    }
}
