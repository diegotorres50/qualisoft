<?php

namespace Qualisoft\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; //@diegotorres50: necesario para las anotaciones de rutas 
use Symfony\Component\HttpFoundation\Response; //@diegotorres50: para el response hello world
use Symfony\Component\HttpFoundation\Request; //@diegotorres50: necesario para validar el login con sesiones
use Qualisoft\AppBundle\Config\Config; //@diegotorres50: de aqui se carga el array de roles para validar el acceso de usuario
use Qualisoft\AppBundle\Model\Model; //@diegotorres50: la logica del negocio para trabajar con mysql


class UserController extends Controller
{
    use DefaultTrait; //Para nuestras funciones custom del controlador

	 /**
     * @Route("admin/user/list/{offset}/{row_count}", name="qualisoft_admin_user_list", requirements={"offset" = "\d+", "row_count" = "\d+"}, defaults={"offset" = 0, "row_count" = 5})   
     */
    public function indexAction(Request $request, $offset, $row_count)
    {
        //SOLO PARA USUARIOS MASTER

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

        } elseif(!$session->has("userRole") || !in_array('MASTER', Config::$ROLES[$session->get("userRole")])) { //Si no se tiene el role de usuario en la sesion o no esta en el array de configuracion de qualisoft

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

        //Instanciamos el modelo de conexion mysql usando el modelo de conexion
        $m = new Model(
            $this->container->getParameter('database_name'), 
            $this->container->getParameter('database_user'),
            $this->container->getParameter('database_password'),
            $this->container->getParameter('database_host')
        );

        //Seteamos en un array los parametros del query para mysql
        $values = array();

        //Seteamos que tabla vamos a consultar
        $values['TABLE'] = 'Users';

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

        //Seteamos el paginador de la consulta para mysql
        $values['LIMIT'] = array( 
            'OFFSET' => $offset, //Desde la fila que se desea mostrar. Esto es un parametro de la ruta
            'ROW_COUNT' => $row_count //Cantidad maximo de registros a traer. Esto es un parametro de la ruta
            );

        //Tratamos de consultar la lista de usuarios en la tabla de mysql
        $getUsersList = $m->getDataFromSingleTable($values);

        //Si el query falla mostramos un error
        if (!empty($getUsersList) && is_array($getUsersList) && isset($getUsersList['errorMsg'])) {
            $this->get('session')->getFlashBag()->add(
                        'error_msg',
                        $getUsersList['errorMsg']
                    );
        }    

        //Si no hay registros, mostramos un warning
        if (isset($getUsersList['rows_found']) && empty($getUsersList['rows_found'])) {
            $this->get('session')->getFlashBag()->add(
                        'warning_msg',
                        'No se encontraron registros.'
                    );
        } 



        //var_dump(json_encode($_tmp, true)); exit;
        $_pages_total = ceil($getUsersList['total'] / $row_count);

        $_current_page = ceil(($offset + 1) / $row_count);

        // ... renderiza la vista ...
        // OJO, CUANDO SE USA MAS DE DOS NIVELES DE DIRECTORIOS, SE DEBE USAR EL SLASH /
        return $this->render('QualisoftAppBundle:Admin/User:list.html.twig', 
            array(
                'view_info' => $view_info, //Datos estaticos y generales informativos de la vista
                'rows_found' => $getUsersList['rows_found'], //Aaray de filas encontradas del query
                'total' => $getUsersList['total'], //Total de registros en la tabla sin limitar
                'cols' => array_values($values['FIELDS']), //Nombres de campos que se muestran en la grilla
                'pages_total' => $_pages_total, //Paginacion: total de paginas que agrupan los registros en la vista
                'current_page' => $_current_page, //Pagina actual o grupo actual del paginador para destacar la pagina actual en la vista
                'page_records' => $row_count, //Paginacion: cantidad de registros que se muestran por pagina
                'pagination' => $this->getPagination($_pages_total, $_current_page, $row_count, 'qualisoft_admin_user_list')
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
}