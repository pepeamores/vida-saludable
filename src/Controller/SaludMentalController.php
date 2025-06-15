<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use MongoDB\Client;

class SaludMentalController extends AbstractController
{
    #[Route('/salud-mental', name: 'salud_mental')]
    public function index(Request $request, SessionInterface $session): Response
    {
        $resultado = null;
        $ejercicio = '';
        $duracion = 0;

        if ($request->isMethod('POST')) {
            $animo = $request->request->get('animo');
            $estres = (int)$request->request->get('estres');
            if ($estres <= 3) {
                $resultado = "¡Buen trabajo! Tu nivel de estrés es bajo. Mantén tus hábitos saludables.";
                $ejercicio = "Respiración consciente";
                $duracion = 60;
            } elseif ($estres <= 7) {
                $resultado = "Tu nivel de estrés es moderado. Te recomendamos relajarte un poco.";
                $ejercicio = "Respiración profunda";
                $duracion = 120;
            } else {
                $resultado = "Tu nivel de estrés es alto. Haz una pausa y realiza este ejercicio.";
                $ejercicio = "Meditación guiada";
                $duracion = 180;
            }

            if ($session->has('user_id')) {
                $client = new Client("mongodb+srv://usuario1:arshak2003@proyectomongo.vfdni.mongodb.net/");
                $db = $client->vida_saludable;
                $db->mental->insertOne([
                    'usuario_id' => $session->get('user_id'),
                    'fecha' => date('Y-m-d'),
                    'animo' => $animo,
                    'estres' => $estres
                ]);
            }
        }

        return $this->render('main/salud_mental.html.twig', [
            'resultado' => $resultado,
            'ejercicio' => $ejercicio,
            'duracion' => $duracion,
        ]);
    }

    #[Route('/grafica-mental', name: 'grafica_mental')]
    public function graficaMental(SessionInterface $session): Response
    {
        if (!$session->has('user_id')) {
            return $this->json(['error' => 'No autenticado']);
        }

        $client = new Client("mongodb+srv://usuario1:arshak2003@proyectomongo.vfdni.mongodb.net/");
        $db = $client->vida_saludable;
        $coleccion = $db->mental;

        $registros = $coleccion->find(['usuario_id' => $session->get('user_id')], ['sort' => ['fecha' => 1]]);

        $fechas = [];
        $estres = [];
        $animo = [];
        $animo_num = [];

        $mapaAnimo = [
            'Feliz' => 10,
            'Tranquilo' => 8,
            'Cansado' => 5,
            'Ansioso' => 4,
            'Triste' => 2,
            'Enojado' => 1
        ];

        foreach ($registros as $r) {
            $fechas[] = isset($r['fecha']) ? date('d/m/Y', strtotime($r['fecha'])) : '';
            $estres[] = isset($r['estres']) ? (int)$r['estres'] : 0;
            $animo[] = $r['animo'] ?? '';
            $animo_num[] = isset($mapaAnimo[$r['animo']]) ? $mapaAnimo[$r['animo']] : 0;
        }

        return $this->json([
            'fechas' => $fechas,
            'estres' => $estres,
            'animo' => $animo,
            'animo_num' => $animo_num
        ]);
    }
}