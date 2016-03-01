<?php

namespace Qualisoft\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; //@diegotorres50: necesario para las anotaciones de rutas 
use Symfony\Component\HttpFoundation\Response; //@diegotorres50: para el response hello world y los formularios
use Symfony\Component\HttpFoundation\Request; //@diegotorres50: necesario para validar el login con sesiones
use Qualisoft\AppBundle\Config\Config; //@diegotorres50: de aqui se carga el array de roles para validar el acceso de usuario
use Qualisoft\AppBundle\Model\Model; //@diegotorres50: la logica del negocio para trabajar con mysql

use Symfony\Component\Validator\Constraints\NotBlank; //@diegotorres50: para las restricciones de campos vacios
class UserController extends Controller
{
    use DefaultTrait; //Para nuestras funciones custom del controlador

	 /**
     * @Route("admin/user/list/{offset}/{row_count}", name="qualisoft_admin_user_general", requirements={"offset" = "\d+", "row_count" = "\d+"}, defaults={"offset" = 0, "row_count" = 5})   
     */
    public function indexAction(Request $request, $offset, $row_count)
    {
        //@diegotorres50 says: usaremos $offset y $row_count para controlar la paginacion de 
        //resultados en las consultas a mysql

        //DEBEMOS CONTROLAR QUE ESTA TEMPLATE SOLO SEA VISIBLE PARA USUARIOS MASTER

        /**
         * Inicia logica verificacion de autenticaccion y acceso mediante las sesiones.
         */

        $session=$request->getSession(); //Instanciamos la sesion del navegador

        if(!$session->has("userId")) //Si en la sesion del browser no esta seteada la variable userId
        {
            //Usamos getFlashBag() para renderizar la alerta de que no esta logueado
            $this->get('session')->getFlashBag()->add(
                               'warning_msg',
                               'Debe estar logueado para ver este contenido.'
                           );
            
            //Dirigimos al login para que haga su autenticacion primero
            return $this->redirect($this->generateUrl('qualisoft_security_login'));

        } elseif(!$session->has("userRole") || !in_array('MASTER', Config::$ROLES[$session->get("userRole")])) { //Si no se tiene el role de usuario en la sesion o no esta en el array de configuracion de qualisoft es porque no es un usuario con perfil de MASTER

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

        /*
         * Inicia logica para definir formulario de busqueda basica       
        */
        $defaultData = array('message' => 'Type your message here');

        //Para ayudar la logica del formulario, seteramos un query string para que al cargar la pagina
        //sepamos que debemos conservar los criterios de la busqueda
        $form_basicLookup = $this->createFormBuilder($defaultData)
            ->setAction($this->generateUrl('qualisoft_admin_user_general', array('filter'=>'enabled')))
            ->setMethod('POST')
            //->setAttribute('class', 'form-horizontal')) Tarea: como agregar atriburos custom al form tag
            ->add('keyword', 'text', array(
                'constraints' => array(
                    new NotBlank(
                        array(
                            'message' => 'Por favor indique un criterio de busqueda.')
                        ),
                 ),
                'label' => 'Buscar en todos los campos*', 
                'label_attr' => array('class' => 'control-label col-md-3'),
                'mapped' => true, //el campo dejar de ser omitido al leer o escribir el objeto, en false el valor no aparece en el array de datos obtenidos
                'required' => true,
                'error_bubbling' => true,
                'attr' => array(
                    'class' => 'form-control',
                    'data-validate-words' => '1', //Valida campo en javascript, https://github.com/yairEO/validator
                    'placeholder' => 'keyword.',
                    ),                
                'empty_data'  => null,         
                )
            )
            ->add('sendData', 'submit', array(
                'attr' => array(
                    'class' => 'btn btn-primary',
                    ),
                'label' => 'Buscar',
                )
             )      
            ->getForm();
     
        /**
         *  El método handleRequest() detecta que el formulario no se ha enviado y por tanto, no hace nada.
         *  Cuando el usuario envía el formulario, el método handleRequest() lo detecta y guarda inmediatamente los datos enviados en las propiedades task y dueDate del objeto $task. 
         */
        $form_basicLookup->handleRequest($request);
     
        /**
         * devuelve false si el formulario no se ha enviado
         */
        if ($form_basicLookup->isValid()) {

               if($request->getMethod()=="POST")
               {

                    /*
                    //@diepgotorres50: esta es una opcion para capturar los campos enviados del formulario    
                    $keyword=$request->get("keyword");
                    */ 
                   
                    $data = $form_basicLookup->getData(); //Esto recupera en un array las llaves y valores de los campos enviakdos en el formulario

                    /*Obtenemos el criterio de busqueda enviado por el formulario*/
                    $keyword = $data['keyword'];                  

                    //Para saber si la consulta tiene criterios basicos
                    $session->set("filter_options", array('alias' => 'basic_lookup', 'query' => $keyword));

                    //nextAction sirve para los casos que necesitemos identificar en que boton
                    //se hizo click cuando el formulario tiene mas de un boton    
                    $nextAction = $form_basicLookup->get('sendData')->isClicked()
                            ? 'task_basiclookup'
                            : 'task_none';

                    //var_dump($keyword); exit;        

                    //return $this->redirectToRoute($nextAction);

               }    

        } else { //ojo aqui cuando este el formulario avanzado hay que validarlo

            if(!$session->has("filter_options")) //Si en la sesion del browser no esta seteada la variable de filtros
            {

                //Para saber si la consulta tiene criterios basicos
                $session->set("filter_options", array('alias' => 'default_lookup', 'query' => ''));            
            }    

        }  

/////////////////////////////////////////


        //Para ayudar la logica del formulario, seteramos un query string para que al cargar la pagina
        //sepamos que debemos conservar los criterios de la busqueda
        $form_newRecord = $this->createFormBuilder($defaultData)
            ->setAction($this->generateUrl('qualisoft_admin_user_general'))
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
            ->add('cancelData', 'reset', array(
                'attr' => array(
                    'class' => 'btn btn-primary',
                    ),
                'label' => 'Cancelar',
                )
             )            
            ->add('sendData', 'submit', array(
                'attr' => array(
                    'class' => 'btn btn-success',
                    ),
                'label' => 'Guardar',
                )
             )      
            ->getForm();
     
        /**
         *  El método handleRequest() detecta que el formulario no se ha enviado y por tanto, no hace nada.
         *  Cuando el usuario envía el formulario, el método handleRequest() lo detecta y guarda inmediatamente los datos enviados en las propiedades task y dueDate del objeto $task. 
         */
        //$form_newRecord->handleRequest($request);


/////////////////////////////////////////        


        /*
         * Termina logica para definir formulario de busqueda basica       
        */

        //Instanciamos el modelo de conexion mysql usando el modelo de conexion
        $m = new Model(
            $this->container->getParameter('database_name'), 
            $this->container->getParameter('database_user'),
            $this->container->getParameter('database_password'),
            $this->container->getParameter('database_host')
        );

        //Tratamos de recuperar los nombres de las columnas desde mysql
        $_getColumnNames = $this->getColumnNames($m, 'view_users', $this->container->getParameter('database_name'));

        if (!empty($_getColumnNames) && is_array($_getColumnNames) && isset($_getColumnNames['errorMsg'])) {
            $this->get('session')->getFlashBag()->add(
                        'error_msg',
                        $_getColumnNames['errorMsg']
                    );
        }
        //

        /**
         * Inicia verificacion de query string para definir la logica por defecto
         */

        //Recorgemos el query string de la url para saber si la pagina la cargamos con filtros
        $filter_status = $request->query->get('filter'); // get a $_GET parameter from query string

        if(($filter_status == 'disabled')) {
            //En caso de que el usuario anule los filtros debemos borrar la variable seteada en la sesion
            $session->remove("filter_options");
        }

        /**
         * Termina verificacion de query string para definir la logica por defecto
         */

        if($session->has("filter_options")) //Si en la sesion del browser no esta seteada la variable de filtros para la consulta
        {
            $_filter_options = $session->get("filter_options");

            if (!empty($_filter_options) && is_array($_filter_options) && isset($_filter_options['alias']) && isset($_filter_options['query'])) {

                if($_filter_options['alias'] == 'basic_lookup') {

                    //Tratamos de recuperar los nombres de las columnas desde mysql
                    $_getRecordsFrom = $this->getRecordsFrom($m, 'view_users', $this->container->getParameter('database_name'), $_filter_options['query'], '', $offset, $row_count);

                } else {

                    //Tratamos de recuperar los nombres de las columnas desde mysql
                    $_getRecordsFrom = $this->getRecordsFrom($m, 'view_users', $this->container->getParameter('database_name'), '', '', $offset, $row_count);

                }
            }            

        } else {

            //Tratamos de recuperar los nombres de las columnas desde mysql
            $_getRecordsFrom = $this->getRecordsFrom($m, 'view_users', $this->container->getParameter('database_name'), '', '', $offset, $row_count);            
        }  

        $_getRecords = $_getRecordsFrom;

        //Si el query falla mostramos un error
        if (!empty($_getRecords) && is_array($_getRecords) && isset($_getRecords['errorMsg'])) {
            $this->get('session')->getFlashBag()->add(
                        'error_msg',
                        $_getRecords['errorMsg']
                    );
        }    

        //Si no hay registros, mostramos un warning
        if (isset($_getRecords['rows_found']) && empty($_getRecords['rows_found'])) {
            $this->get('session')->getFlashBag()->add(
                        'warning_msg',
                        'No se encontraron registros.'
                    );
        } 

        //var_dump(json_encode($_tmp, true)); exit;
        $_pages_total = ceil($_getRecords['total'] / $row_count);

        $_current_page = ceil(($offset + 1) / $row_count);

        // ... renderiza la vista ...
        // OJO, CUANDO SE USA MAS DE DOS NIVELES DE DIRECTORIOS, SE DEBE USAR EL SLASH /
        return $this->render('QualisoftAppBundle:Admin/User:general.html.twig', 
            array(
                'rows_found' => $_getRecords['rows_found'], //Aaray de filas encontradas del query
                'total' => $_getRecords['total'], //Total de registros en la tabla sin limitar
                //'cols' => array_values($values['FIELDS']), //Nombres de campos que se muestran en la grilla
                'cols' => $_getColumnNames, //Nombres de campos que se muestran en la grilla
                'pages_total' => $_pages_total, //Paginacion: total de paginas que agrupan los registros en la vista
                'current_page' => $_current_page, //Pagina actual o grupo actual del paginador para destacar la pagina actual en la vista
                'page_records' => $row_count, //Paginacion: cantidad de registros que se muestran por pagina
                'pagination' => $this->getPagination($_pages_total, $_current_page, $row_count, 'qualisoft_admin_user_general'),
                'form_basic_lookup' => $form_basicLookup->createView(),
                'form_new_record' => $form_newRecord->createView()
                )
            );
    }

     /**
     * @Route("admin/user/purge/{id_value}/{token}/{table_name}/{column_name}", name="qualisoft_admin_user_purge", defaults={"table_name" = "Users", "column_name" = "user_id"})   
     */
    public function purgeAction(Request $request, $id_value, $token, $table_name, $column_name)
    {

        //DEBEMOS CONTROLAR QUE ESTA TEMPLATE SOLO SEA VISIBLE PARA USUARIOS MASTER

        /**
         * Inicia logica verificacion de autenticaccion y acceso mediante las sesiones.
         */

        $session=$request->getSession(); //Instanciamos la sesion del navegador

        if(!$session->has("userId")) //Si en la sesion del browser no esta seteada la variable userId
        {
            //Usamos getFlashBag() para renderizar la alerta de que no esta logueado
            $this->get('session')->getFlashBag()->add(
                               'warning_msg',
                               'Debe estar logueado para ver este contenido.'
                           );
            
            //Dirigimos al login para que haga su autenticacion primero
            return $this->redirect($this->generateUrl('qualisoft_security_login'));

        } elseif(!$session->has("userRole") || !in_array('MASTER', Config::$ROLES[$session->get("userRole")])) { //Si no se tiene el role de usuario en la sesion o no esta en el array de configuracion de qualisoft es porque no es un usuario con perfil de MASTER

            $this->get('session')->getFlashBag()->add(
                               'warning_msg',
                               'El usuario no tiene suficientes permisos ' . $session->get("userRole") . ' para purgar el registro.'
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
         * Verificamos que el token sea valido para poder purgar al usuario
         */

        if(md5(md5($token)) != md5(md5($id_value))) //Si el token enviado no es valido
        {
            //Usamos getFlashBag() para renderizar la alerta de que token no es valido
            $this->get('session')->getFlashBag()->add(
                               'error_msg',
                               'Token no es valido.'
                           );
            
            //Dirigimos a la vista de usuarios
            return $this->redirect($this->generateUrl('qualisoft_admin_user_general'));

        }


        //Instanciamos el modelo de conexion mysql usando el modelo de conexion
        $m = new Model(
            $this->container->getParameter('database_name'), 
            $this->container->getParameter('database_user'),
            $this->container->getParameter('database_password'),
            $this->container->getParameter('database_host')
        );

        //Tratamos de cambiar el estado del registro para ocultarlo de las consultas generales
        $_setToPurge = $this->setToPurge($m, $table_name, $column_name, $id_value);

        if (!empty($_setToPurge) && is_array($_setToPurge) && isset($_setToPurge['errorMsg'])) {
            $this->get('session')->getFlashBag()->add(
                        'error_msg',
                        $_setToPurge['errorMsg']
                    );
        } else {
            //Sino tenemos variables de error, confirmamos exito
            $this->get('session')->getFlashBag()->add(
                        'success_msg',
                        'El registro: ' . $id_value . ' ha sido marcado para purga!!!'
                );
        }
        //
        
        return $this->redirect($this->generateUrl('qualisoft_admin_user_general'));

    }

     /**
     * @Route("admin/user/new", name="qualisoft_admin_user_new_module")   
     */
    public function new_moduleAction(Request $request)
    {

        //DEBEMOS CONTROLAR QUE ESTA TEMPLATE SOLO SEA VISIBLE PARA USUARIOS MASTER

        /**
         * Inicia logica verificacion de autenticaccion y acceso mediante las sesiones.
         */

        $session=$request->getSession(); //Instanciamos la sesion del navegador

        if(!$session->has("userId")) //Si en la sesion del browser no esta seteada la variable userId
        {
            //Usamos getFlashBag() para renderizar la alerta de que no esta logueado
            $this->get('session')->getFlashBag()->add(
                               'warning_msg',
                               'Debe estar logueado para ver este contenido.'
                           );
            
            //Dirigimos al login para que haga su autenticacion primero
            return $this->redirect($this->generateUrl('qualisoft_security_login'));

        } elseif(!$session->has("userRole") || !in_array('MASTER', Config::$ROLES[$session->get("userRole")])) { //Si no se tiene el role de usuario en la sesion o no esta en el array de configuracion de qualisoft es porque no es un usuario con perfil de MASTER

            $this->get('session')->getFlashBag()->add(
                               'warning_msg',
                               'El usuario no tiene suficientes permisos ' . $session->get("userRole") . ' para crear registros.'
                           );
            
            // redirect the user to where they were before the login process begun.
            $referer_url = $request->headers->get('referer');
                        
            if(!empty($referer_url)) return $this->redirect($request->headers->get('referer'));
            else return $this->redirect($this->generateUrl('qualisoft_default_homepage')); //Por defecto al home sino hay referrer
        }

        /**
         * Termina logica verificacion de autenticaccion y acceso.
         */

        // Logica general

        //
        
        // ... renderiza la vista ...
        // OJO, CUANDO SE USA MAS DE DOS NIVELES DE DIRECTORIOS, SE DEBE USAR EL SLASH /
        return $this->render('QualisoftAppBundle:Admin/User:new_module.html.twig', 
            array(
                'data' => 'cualquier cosa'
                )
            );

    }

}

//Funciones custom de @diegotorres50 para incluiir en el contrlador
trait DefaultTrait
{
    //Metodo para armar el paginador
    protected function getPagination($_pages_total, $_current_page, $page_records, $route_path)
    {
        //La paginacion la guardamos en un array
        $_pagination = array();

        //La paginacion muestra por defecto las tres primeras paginas, se valida con if
        //si la pagina existe para mostrarla
        if($_pages_total >= 1) 
            $_pagination[] = 1; //Pagina primera
        if($_pages_total >= 2) 
            $_pagination[] = 2; //Pagina segunda
        if($_pages_total >= 3) 
            $_pagination[] = 3; //Pagina tercera

        //$_pagination[] = '...'; //Para el intervalo entre paginas no secuenciales

        //La paginacion muestra por defecto las tres ultimas paginas, se valida con if
        //si la pagina existe para mostrarla
        if($_pages_total >= ($_pages_total - 2)) 
            $_pagination[] = $_pages_total - 2; //Pagina ante penultima
        if($_pages_total >= ($_pages_total - 1)) 
            $_pagination[] = $_pages_total - 1; //Pagina penultima
        if($_pages_total >= ($_pages_total)) 
            $_pagination[] = $_pages_total; //Pagina ultima

        //$_pagination[] = '...'; //Para el intervalo entre paginas no secuenciales

        //La paginacion muestra por defecto la pagina actual, la pagina anterior a la actual 
        //y la pagina posterior a la actualse valida con if
        //si la pagina existe para mostrarla
        if($_pages_total >= ($_current_page - 1)) 
            $_pagination[] = $_current_page - 1; //Pagina anterior a la actual
        if($_pages_total >= ($_current_page)) 
            $_pagination[] = $_current_page; //Pagina actual
        if($_pages_total >= ($_current_page + 1)) 
            $_pagination[] = $_current_page + 1; //Pagina posterior a la actual       

        //La logica de la paginacion podria generar paginas negativas, entonces filtramos el array para
        //remover los valores negativos
        $_pagination = array_filter($_pagination, function ($v) {
          return $v > 0 || $v == '...';
        });

        //$_pagination[] = '...'; //Para el intervalo entre paginas no secuenciales
        //

        //var_dump($_pagination); exit;

        //Ordenamos las paginas
        asort($_pagination);

        //Removemos numeros de pagina duplicados
        $_pagination = array_unique($_pagination);        

        //Array temporal para agregar los puntos suspensivos entre rangos de pagina
        $_tmp = array();

        //Rseteamos las keys del array a 0, 1, 2, ... para controlar la logica
        $_pagination = array_values($_pagination);

        //var_dump($_pagination); exit;

        foreach ($_pagination as $k => $v) {
            
            //Despues de haber resetado la clave 0 y la pagina 0, evitamos mostrarla
            if($v > 0) {
                //echo 'k: ' . $k . ' - v: ' . $v . ' - array: ' . $_pagination[$k] . '<br>'; 
                //Armamos el data del paginador que usara la vista
                $_tmp[] = array(
                    'page_number' => $v, 
                    'offset' => ($v - 1) * $page_records, //Desde la fila que se desea mostrar desde mysql. Esto es un parametro de la ruta
                    'row_count' => $page_records, //Cantidad de registros que se desean traer desde mysql
                    'path' => $route_path //Alias de la ruta a donde va la pagina
                    );
            }
                
            //Evitamos el warning del ultimo elemento para que el $k + 1 no lo genere    
            if(($k < (array_pop(array_keys($_pagination))))) { //Siempre que la clave sea menor que el ultimo elemento del array (la ultima clave)
                
                if($_pagination[$k + 1] != ($v + 1) ) { //Si el valor del siguiente elemento es diferente a la pagina esperada, significa que se rompe la secuencia de paginas e inicia el nuevo bloque o rango de paginas
                    $_tmp[] = array('page_number' => '...'); //Mostramos los puntos suspensivos
                }    
            }   
        }

        //Retomamps el paginador ya optimizado
        $_pagination = $_tmp;

        //var_dump($_pagination);

        //Retornamos el array con la paginacion
        return $_pagination;

    }

    //Metodo para obtener los nombres de las columnas por defecto de la vista en mysql y mostrarlos en la grilla
    protected function getColumnNames($_conexion, $_viewOrTable, $_databaseName)
    {
        //Recuperamos los nombres de las columnas que se mostraran en la grilla
        $param_values = array(
            'TABLE' => $_viewOrTable,
            'database_name' => $_databaseName
        );

        //Tratamos de recuperar los nombres de las columnas desde mysql
        $_result = $_conexion->getColumnNames($param_values);

        //Retornamos los nombres de las columnas
        return $_result;

    }

    //Metodo para obtener los registros por defecto de la vista en mysql y mostrarlos en la grilla
    //la primera vez.
    protected function getRecordsByDefault($_conexion, $_viewOrTable, $_offset, $row_count)
    {

        //Seteamos en un array los parametros del query para mysql
        $values = array();

        //Seteamos que tabla vamos a consultar
        $values['VIEW'] = $_viewOrTable;

        /*@diegotorres50 por defecto  usaremos los campos y orden de la vista

        //Seteamos los alias de los campos de deseamos traer en el query y un lbl para la vista
        $values['FIELDS'] = array( //identificador_campo => aliascustomizado_campo
            'user_id' => 'id',
            'user_document' => 'documento',
            'user_status' => 'estado',
            'user_name' => 'nombre',
            'user_mail' => 'mail',
            'user_language' => 'idioma',
            'user_lastactivation' => 'activado_desde',
            'user_role' => 'perfil'
            );

        //$values['WHERE'] = 'WHERE user_id = \'diegotorres50\''; //Esto no es necesario

        //Seteamos el ordenamiento por defecto del query
        $values['ORDER_BY'] = array( 
            'user_id desc' //ordenar por id para traerlo alfabeticamente
            );
        *//////////////////////////////////////////////////

        //Seteamos el paginador de la consulta para mysql
        $values['LIMIT'] = array( 
            'OFFSET' => $_offset, //Desde la fila que se desea mostrar. Esto es un parametro de la ruta
            'ROW_COUNT' => $row_count //Cantidad maximo de registros a traer. Esto es un parametro de la ruta
            );

        //Tratamos de consultar la lista de usuarios en la vista de mysql
        $_result = $_conexion->getDataFromSingleView($values);

        //Retornamos los nombres de las columnas
        return $_result;

    }        

    //Metodo para obtener todos los registros encontrados en una vista o tabla en mysql y mostrarlos en la grilla
    protected function getRecordsFrom($_conexion, $_viewOrTable, $_databaseName, $_search , $_orderby , $_offset, $_row_count)
    {
        //Definimos en un array los parametros
        $param_values = array(
            'TABLE' => $_viewOrTable,
            'database_name' => $_databaseName,
            'SEARCH' => $_search,
            'ORDER_BY' => $_orderby,
            'OFFSET' => $_offset,
            'ROW_COUNT' => $_row_count
        );

        //Tratamos de recuperar los registros desde mysql
        $_result = $_conexion->getRecordsFrom($param_values);

        //Retornamos los nombres de las columnas
        return $_result;

    }

    //Metodo para cambiar el estado de purga a true de un registro
    protected function setToPurge($_conexion, $_table_name, $_column_name, $_id_value)
    {
        //Seteamos el objeto parametro que enviaremos a mysql
        $param_values = array(
            'TABLE' => $_table_name,
            'FIELD' => $_column_name,
            'VALUE' => $_id_value
        );

        //Tratamos de cambiar el estado del campo purge en mysql
        $_result = $_conexion->setToPurge($param_values);

        //Retornamos los nombres de las columnas
        return $_result;

    }

}