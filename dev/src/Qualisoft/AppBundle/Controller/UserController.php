<?php

namespace Qualisoft\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; //@diegotorres50: necesario para las anotaciones de rutas 
use Symfony\Component\HttpFoundation\Response; //@diegotorres50: para el response hello world
use Symfony\Component\HttpFoundation\Request; //@diegotorres50: necesario para validar el login con sesiones
use Qualisoft\AppBundle\Config\Config; //@diegotorres50: de aqui se carga el array de roles para validar el acceso de usuario

class UserController extends Controller
{
	 /**
     * @Route("admin/user", name="qualisoft_admin_user_homepage") 
     */
    public function indexAction(Request $request)
    {
        /**
         * Inicia logica verificacion de autenticaccion y acceso.
         */

        $session=$request->getSession();

        if(!$session->has("userId"))
        {
            $this->get('session')->getFlashBag()->add(
                               'warning_msg',
                               'Debe estar logueado para ver este contenido.'
                           );
            
            //Dirigimos al login
            return $this->redirect($this->generateUrl('qualisoft_security_login'));

        } elseif(!$session->has("userRole") || !in_array($session->get("userRole"), Config::$ROLES['MASTER'])) { //Si no se tiene el role de usuario en la sesion o no esta en el array de configuracion de qualisoft

            $this->get('session')->getFlashBag()->add(
                               'warning_msg',
                               'El usuario no tiene suficientes permisos ' . $session->get("userRole") . ' para acceder a este módulo.'
                           );
            
            // redirect the user to where they were before the login process begun.
            $referer_url = $request->headers->get('referer');
                        
            if(!empty($referer_url)) return $this->redirect($request->headers->get('referer'));
            else return $this->redirect($this->generateUrl('qualisoft_default_homepage')); //Por defecto al home sino hay referrer
        }

        /**
         * Termina logica verificacion de autenticaccion y acceso.
         */        

        /**
         * [$view_info description datos generales de la vista a renderizar]
         * @var array
         */
        $view_info = array(
                    'title' => 'Usuarios',
                    'module_title' => 'Usuarios',
                    'module_subtitle' => 'Lista de Usuarios',
                    'module_lead' => 'Modifique el usuario.', 
                    );

        // ... renderiza la vista ...
        // OJO, CUANDO SE USA MAS DE DOS NIVELES DE DIRECTORIOS, SE DEBE USAR EL SLASH /
        return $this->render('QualisoftAppBundle:Admin/User:list.html.twig', 
            array(
                'view_info' => $view_info
                )
            );
    }
            
}
