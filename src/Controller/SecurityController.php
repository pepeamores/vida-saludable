<?php

namespace App\Controller;

use App\Service\MongoService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(Request $request, SessionInterface $session, MongoService $mongo): Response
    {
        $error = null;

        if ($request->isMethod('POST')) {
            $usuario = $request->request->get('usuario');
            $password = $request->request->get('password');

            $coleccion = $mongo->getColeccion('usuarios');
            $user = $coleccion->findOne(['usuario' => $usuario]);

            if (!$user) {
                $error = 'Usuario no encontrado.';
            } elseif (!password_verify($password, $user['password'])) {
                $error = 'Contraseña incorrecta.';
            } else {
                // Usuario válido, guardar sesión
                $session->set('user_id', (string) $user['_id']);
                $session->set('nombre', $user['nombre'] ?? '');
                $session->set('apellidos', $user['apellidos'] ?? '');
                $session->set('fecha_nacimiento', $user['fecha_nacimiento'] ?? null);
                $session->set('altura', $user['altura'] ?? '');
                $session->set('peso', $user['peso'] ?? '');
                $session->set('sexo', $user['sexo'] ?? '');
                $session->set('email', $user['email'] ?? '');
                $session->set('usuario', $user['usuario'] ?? '');
                $session->set('rol', $user['rol'] ?? 'usuario');

                if (($user['rol'] ?? 'usuario') === 'admin') {
                    return $this->redirectToRoute('admin_panel');
                }
                return $this->redirectToRoute('main_inicio');
            }
        }

        return $this->render('security/login.html.twig', [
            'error' => $error,
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(SessionInterface $session): Response
    {
        // Borra todos los datos de la sesión del usuario
        $session->clear();

        // Redirige al login u otra página pública
        return $this->redirectToRoute('app_login');
    }
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, MongoService $mongo): Response
    {
        $mensaje = null;
        $coleccion = $mongo->getColeccion('usuarios');

        if ($request->isMethod('POST')) {
            $altura = $request->request->get('altura');
            $peso = $request->request->get('peso');
            $email = $request->request->get('email');

            // Validación backend
            if (!is_numeric($altura) || $altura < 30 || $altura > 300) {
                $mensaje = "La altura debe ser un número válido entre 30 y 300 cm.";
            } elseif (!is_numeric($peso) || $peso < 10 || $peso > 500) {
                $mensaje = "El peso debe ser un número válido entre 10 y 500 kg.";
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $mensaje = "El correo electrónico no es válido.";
            } else {
                $data = [
                    'nombre'     => trim($request->request->get('nombre')),
                    'apellidos'  => trim($request->request->get('apellidos')),
                    'fecha_nacimiento' => $request->request->get('fecha_nacimiento'),
                    'altura'     => (float)$altura,
                    'peso'       => (float)$peso,
                    'sexo'       => $request->request->get('sexo'),
                    'email'      => strtolower(trim($email)),
                    'usuario'    => trim($request->request->get('usuario')),
                    'password'   => password_hash($request->request->get('password'), PASSWORD_DEFAULT),
                    'rol'        => 'usuario',
                    'activo'     => true,
                    'fecha_registro' => new \MongoDB\BSON\UTCDateTime()
                ];

                // Comprobar si ya existe el usuario o email
                $existe = $coleccion->findOne([
                    '$or' => [
                        ['email' => $data['email']],
                        ['usuario' => $data['usuario']]
                    ]
                ]);

                if ($existe) {
                    $mensaje = "⚠️ El correo o usuario ya están registrados.";
                } else {
                    $coleccion->insertOne($data);
                    $mensaje = "✅ Registro exitoso. Ahora puedes iniciar sesión.";
                }
            }
        }

        return $this->render('security/register.html.twig', [
            'mensaje' => $mensaje
        ]);
    }
}
