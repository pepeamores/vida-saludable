<?php

namespace App\Controller;

use App\Service\MongoService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    #[Route('/', name: 'root')]
    public function rootRedirect(SessionInterface $session): Response
    {
        if ($session->has('user_id')) {
            return $this->redirectToRoute('main_inicio');
        }
        return $this->redirectToRoute('bienvenida');
    }

    #[Route('/inicio', name: 'main_inicio')]
    public function inicio(SessionInterface $session): Response
    {
        if (!$session->has('user_id')) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('main/inicio.html.twig', [
            'datos' => [
                'user_id' => $session->get('user_id'),
                'nombre' => $session->get('nombre'),
                'apellidos' => $session->get('apellidos'),
                'fecha_nacimiento' => $session->get('fecha_nacimiento'),
                'altura' => $session->get('altura'),
                'peso' => $session->get('peso'),
                'sexo' => $session->get('sexo'),
                'email' => $session->get('email'),
                'usuario' => $session->get('usuario'),
                'rol' => $session->get('rol'),
                'edad' => $this->calcularEdad($session->get('fecha_nacimiento'))
            ]
        ]);
    }

    #[Route('/bienvenida', name: 'bienvenida')]
    public function bienvenida(SessionInterface $session): Response
    {
        $session->clear();
        return $this->render('main/bienvenida.html.twig');
    }



    #[Route('/salud-mental', name: 'salud_mental')]
    public function saludMental(): Response
    {
        return $this->render('main/salud_mental.html.twig');
    }

    #[Route('/register', name: 'app_register')]
    public function register(): Response
    {
        return $this->render('security/register.html.twig');
    }

    #[Route('/opiniones', name: 'ver_opiniones')]
    public function verOpiniones(MongoService $mongo): Response
    {
        $coleccion = $mongo->getColeccion('opiniones');
        $opiniones = $coleccion->find([], ['sort' => ['fecha' => -1]]);

        return $this->render('main/opiniones.html.twig', [
            'opiniones' => $opiniones,
        ]);
    }

    #[Route('/guardar-opinion', name: 'guardar_opinion', methods: ['POST'])]
    public function guardarOpinion(Request $request, SessionInterface $session, MongoService $mongo): Response
    {
        $coleccion = $mongo->getColeccion('opiniones');
        $coleccion->insertOne([
            'usuario_id' => $session->get('user_id'),
            'nombre' => $session->get('nombre'), // <-- Añade esta línea
            'puntuacion' => (int) $request->request->get('puntuacion'),
            'comentario' => $request->request->get('comentario'),
            'fecha' => new \MongoDB\BSON\UTCDateTime()
        ]);
        return $this->redirectToRoute('main_inicio');
    }

    #[Route('/actualizar-datos', name: 'actualizar_datos', methods: ['POST'])]
    public function actualizarDatos(Request $request, SessionInterface $session, MongoService $mongo): Response
    {
        if (!$session->has('user_id')) {
            return $this->redirectToRoute('app_login');
        }

        $userId = new \MongoDB\BSON\ObjectId($session->get('user_id'));
        $altura = (float) $request->request->get('altura');
        $peso = (float) $request->request->get('peso');

        $coleccion = $mongo->getColeccion('usuarios');
        $coleccion->updateOne(
            ['_id' => $userId],
            ['$set' => ['altura' => $altura, 'peso' => $peso]]
        );

        $session->set('altura', $altura);
        $session->set('peso', $peso);

        return $this->redirectToRoute('main_inicio');
    }

    private function calcularEdad($fechaTexto): string
    {
        try {
            $fecha = new \DateTime($fechaTexto);
            $hoy = new \DateTime();
            return $hoy->diff($fecha)->y . ' años';
        } catch (\Exception $e) {
            return 'No especificada';
        }
    }
}
