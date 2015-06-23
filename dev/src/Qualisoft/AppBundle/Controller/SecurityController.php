<?php

namespace Qualisoft\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; //@diegotorres50: necesario para las anotaciones de rutas 
use Symfony\Component\HttpFoundation\Request; //@diegotorres50: necesario para el formulario
use Symfony\Component\Validator\Constraints\NotBlank; //@diegotorres50: para las restricciones de campos vacios
use Qualisoft\AppBundle\Model\Model; //@diegotorres50: la logica del negocio para trabajar con mysql

class SecurityController extends Controller
{
	 /**
     * @Route("/login", name="qualisoft_security_login") 
     */
    public function loginAction(Request $request)
    {


        //Si la sesion ya existe, no mostramos el formulario de login
        if($request->getSession()->has("userId"))
        {
            return $this->redirect($this->generateUrl('qualisoft_default_homepage'));
        }    

	    $defaultData = array('message' => 'Type your message here');

	    $form = $this->createFormBuilder($defaultData)
            ->setAction($this->generateUrl('qualisoft_security_login'))
            ->setMethod('POST')
            //->setAttribute('class', 'form-horizontal')) Tarea: como agregar atriburos custom al form tag
	        ->add('userId', 'text', array(
                'constraints' => array(
                    new NotBlank(
                        array(
                            'message' => 'Por favor indique el usuario.')
                        ),
                 ),
                'label' => 'Usuario*', 
                'label_attr' => array('class' => 'control-label col-md-3'),
                'mapped' => true, //el campo dejar de ser omitido al leer o escribir el objeto, en false el valor no aparece en el array de datos obtenidos
                'required' => true,
                'error_bubbling' => true,
                'attr' => array(
                    'class' => 'form-control',
                    'data-validate-words' => '1', //Valida campo en javascript, https://github.com/yairEO/validator
                    'placeholder' => 'Escribe tu usuario.',
                    ),                
                'empty_data'  => null,         
                )
            )
            ->add('userPass', 'password', array(
                'constraints' => array(
                    new NotBlank(
                        array(
                            'message' => 'Por favor indique la clave.')
                        ),
                 ),
                'label' => 'Clave:', 
                'label_attr' => array('class' => 'control-label col-md-3'),
                'mapped' => true, //el campo dejar de ser omitido al leer o escribir el objeto, en false el valor no aparece en el array de datos obtenidos                'required' => true,
                'error_bubbling' => true,
                'attr' => array(
                    'class' => 'form-control',
                    'placeholder' => 'Escribe tu clave.',
                    ),                
                'empty_data'  => null,                 
                )
            )
	        ->add('sendData', 'submit', array(
                'attr' => array(
                    'class' => 'btn btn-success',
                    ),
                'label' => 'Ingresar',
                )
             )      
	        ->getForm();
	 
        /**
         *  El método handleRequest() detecta que el formulario no se ha enviado y por tanto, no hace nada.
         *  Cuando el usuario envía el formulario, el método handleRequest() lo detecta y guarda inmediatamente los datos enviados en las propiedades task y dueDate del objeto $task. 
         */
	    $form->handleRequest($request);
	 
        /**
         * devuelve false si el formulario no se ha enviado
         */
	    if ($form->isValid()) {

               if($request->getMethod()=="POST")
               {

                    /*
                    //@diepgotorres50: esta es una opcion para capturar los campos enviados del formulario    
                    $userId=$request->get("userId");
                    $userPass=$request->get("userPass");
                    */ 
                   
                    $data = $form->getData(); //Esto recupera en un array las llaves y valores de los campos enviakdos en el formulario

                    /*Obtenemos el usuario y clave enviado por el formulario*/
                    $userId = $data['userId'];
                    $userPass = $data['userPass'];                   


                    /*
                     *Obtener datos de cada base de datos indistintamente y siempre que sea necesario,
                     *Ees decir,  accedemos al recurso default, es decir las transacciones que seamos capaces de realizar a través de $em tendrán efecto 
                     *en la base de datos bd1 de la conexión default.
                    */                    
                    $em = $this->getDoctrine()->getManager(); //Ver mas: http://mycyberacademy.com/conectandose-a-varias-bases-de-datos-con-symfony2-doctrine/


                    /*
                     *Parametros del query, basicamente aqui es un arrya con los valores de los campos usuario y clave del formulario
                     *que se compararan con la tabla usuarios de la base de datos
                     */
                    $parameters = array(
                        'userId_' => $userId, 
                        'userPass_' => md5(md5($userPass)) //Esta clave debe estar doblemente encriptada
                    );

                    /**
                     *Definimos la consulta, ver ejemplos: http://doctrine-orm.readthedocs.org/en/latest/reference/query-builder.html y conceptos en
                     *http://gitnacho.github.io/symfony-docs-es/book/doctrine.html
                     */
                    $qualisoft_users_query = $em->createQuery('SELECT qualisoft_users.userId, qualisoft_users.userName FROM QualisoftAppBundle:Users qualisoft_users WHERE qualisoft_users.userId= :userId_ and qualisoft_users.userPass= :userPass_')
                    ->setParameters($parameters)
                    ->setMaxResults(1);
                    

                    try {
                        /**
                         *Ejecutamos la consulta 
                         */
                        //$query_result = $qualisoft_users_query->getResult(); //Devuelve un array con el resultado
                        $query_result = $qualisoft_users_query->getSingleResult(); //Para cuando buscamos un solo resultabdo pero debemos contrlar la excepcion
                    } catch (\Doctrine\Orm\NoResultException $e) {
                        $query_result = null;
                    }

                    /*
                     *Este metodo es de Cesar Cansino, muy al estilo de Doctrine, poco clasico y convencional para los desarrolladores clasicos, pero funciona perfecto
                     */
                    //$qualisoft_users_query=$this->getDoctrine()->getRepository('QualisoftAppBundle:Users')->findOneBy(array("userId"=>$userId,"userPass"=>$userPass));

                    /*Validamos que hacer con el usuario*/
                    if ($query_result) {

                        $session=$request->getSession();

                        $session->set("userId", $query_result['userId']);
                        $session->set("userName", $query_result['userName']);

                        /* Usando y siguiendo el metodo Cesar Cansino seria asi.
                        $session->set("userId", $query_result->getuserId());
                        $session->set("userName", $query_result->getuserName());
                        */    

                        $this->get('session')->getFlashBag()->add(
                                    'success_msg',
                                    'Hola ' . $query_result['userName']
                                );

                        //Insertamos la sesion en la base de datos
                        $login_values = array(
                                     'database_name' => $this->container->getParameter('database_name'),
                                     'login_user_id' => $query_result['userId'],
                                     'login_time' => new \DateTime("now"),
                                     'login_useragent' => 'PENDIENTE',
                                     'login_language' => 'PENDIENTE',
                                     'login_platform' => 'PENDIENTE',
                                     'login_origin' => 'PENDIENTE',
                                     'login_notes' => 'PENDIENTE'
                                     );

                        //Instanciamos el modelon de conexion mysql
                        $m = new Model(
                            $this->container->getParameter('database_name'), 
                            $this->container->getParameter('database_user'),
                            $this->container->getParameter('database_password'),
                            $this->container->getParameter('database_host')
                        );

                        //Tratamos de registrar la sesion en la tabla de logins de mysql
                        $setLogin = $m->setLogin($login_values);

                        if (!empty($setLogin) && is_array($setLogin) && isset($setLogin['errorMsg'])) {
                            $this->get('session')->getFlashBag()->add(
                                        'error_msg',
                                        $setLogin['errorMsg']
                                    );
                        }
                        //Fin de insertamos la sesion en la base de datos


                       return $this->redirect($this->generateUrl('qualisoft_default_homepage'));

                    } else {

                        /*Lanzamos una excepcion aqui
                        throw $this->createNotFoundException(
                            'No existe el usuario '. $userId
                        ); */                                   {
                        
                        $this->get('session')->getFlashBag()->add(
                                    'error_msg',
                                    'Los datos ingresados no son válidos!'
                                );
                        
                        return $this->redirect($this->generateUrl('qualisoft_security_login'));
                    }


                    var_dump($query_result); exit;

                    $nextAction = $form->get('saveAndAdd')->isClicked()
                            ? 'task_new'
                            : 'task_success';

                    return $this->redirectToRoute($nextAction);

               }    

            }   

	    }
	 
        /**
         * [$view_info description datos generales de la vista a renderizar]
         * @var array
         */
        $view_info = array(
                    'title' => 'Login',
                    'module_title' => 'Zona de Seguridad',
                    'module_subtitle' => 'Login de Acceso',
                    'module_lead' => 'Para acceder a todas las funciones debe ingresar sus credenciales.', 
                    );

	    // ... renderiza el formulario ...
        return $this->render('QualisoftAppBundle:Security:login.html.twig', 
            array(
                'form' => $form->createView(),
                'view_info' => $view_info
                )
            );

       
    }
      
	 /**
     * @Route("/logout", name="qualisoft_security_logout") 
     */      
    public function logoutAction(Request $request)
    {
        
        $session=$request->getSession();

        //Si la sesion existe, entonces si la limpiamos
        if($session->has("userId"))
        {
            $session=$request->getSession();
            $session->clear();
            $this->get('session')->getFlashBag()->add(
                                    'success_msg',
                                    'Se ha cerrado sessión exitosamente, gracias por visitarnos'
                                );
        }    

        return $this->redirect($this->generateUrl('qualisoft_security_login'));
    }

}