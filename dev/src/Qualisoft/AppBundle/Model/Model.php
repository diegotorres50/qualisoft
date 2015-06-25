<?php

namespace Qualisoft\AppBundle\Model;

use Symfony\Component\HttpFoundation\Request;

class Model
{

    /* @diegotorres50: la idea es que sea el modelo de negocio para conectar y gestionar la base de datos */
    protected $conexion;

    public function __construct($dbname,$dbuser,$dbpass,$dbhost)
    {
        $mvc_bd_conexion = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

        if (mysqli_connect_errno()) {
            die('No ha sido posible realizar la conexión con la base de datos: '
              . mysqli_connect_error());
        }
         
        $this->conexion = $mvc_bd_conexion;
    }

    public function bd_conexion()
    {

    }

    public function setLogin($values)
    {
        //@diegotorres50: metodo que inserta datos de una nueva sesion de usuario
        //

        
        if(!isset($values) || empty($values) || !is_array($values)) 
            return array('errorMsg' => 'Se esperaba un objeto como parámetro del método');

        //Escapamos las cadenas de texto
        $values['login_user_id'] = mysqli_real_escape_string($this->conexion, $values['login_user_id']);
        $values['login_useragent'] = mysqli_real_escape_string($this->conexion, $values['login_useragent']);
        $values['login_language'] = mysqli_real_escape_string($this->conexion, $values['login_language']);
        $values['login_platform'] = mysqli_real_escape_string($this->conexion, $values['login_platform']);
        $values['login_useragent'] = mysqli_real_escape_string($this->conexion, $values['login_useragent']);
        $values['login_notes'] = mysqli_real_escape_string($this->conexion, $values['login_notes']);

        $sql = "INSERT INTO `" . $values['database_name'] . "`.`logins`
        (
            `login_user_id`,
            `login_time`,

            `login_useragent`,
            `login_language`,
            `login_platform`,
            `login_origin`,

            `login_notes`
        )

        VALUES

        (
            '" . $values['login_user_id'] . "',
            '" . $values['login_time']->format('Y-m-d H:i:s') . "',

            '" . $values['login_useragent'] . "',
            '" . $values['login_language'] . "',
            '" . $values['login_platform'] . "',
            '" . $values['login_useragent'] . "',

            '" . $values['login_notes'] . "'
        );";

        $result = mysqli_query($this->conexion, $sql);

        if(!$result) {

            return array('errorMsg' => 'No ha sido posible realizar el registro de sesión del usuario: ' . mysqli_error($this->conexion));
        }

        //mysqli_close($this->conexion); No cerremos la conexion para reusarla    

        return $result;
     }

    public function getLoginId($values)
    {
        //@diegotorres50: metodo que recupera el id de la sesion del usuario, esto es en un procedimiento de mysql
        //
        
        if(!isset($values) || empty($values) || !is_array($values)) 
            return array('errorMsg' => 'Se esperaba un objeto como parámetro del método');

        //$sql = "SELECT * FROM qualisoft_dev.logins;";
        $sql = "set @param_login_id = 0;"; //Parametro de salida por defecto es cero

        //Escapamos las cadenas de texto
        $values['login_user_id'] = mysqli_real_escape_string($this->conexion, $values['login_user_id']);

        //Ejecutamos el procedimiento
        //No usemos el nombre de la base de datos para evitar errores: nombrebasededatos. procedure(
        $sql .= "call procedure_getLoginId('" . $values['login_user_id'] . "', '" . $values['login_time']->format('Y-m-d H:i:s') . "', @param_login_id);";

        $sql .= "select @param_login_id as _param_login_id;"; //Recogemos el resultado

        /* execute multi query */
        if (mysqli_multi_query($this->conexion, $sql)) {
            do {
                $result_row = array();
                /* store first result set */
                if ($result = mysqli_store_result($this->conexion)) {
                    while ($row = mysqli_fetch_row($result)) {
                        $result_row[] = $row[0];
                    }
                    mysqli_free_result($result);
                }
                /* print divider */
                //if (mysqli_more_results($this->conexion)) {
                //    printf("ESTEESUNSEPARADOR\n");
                //}
            } while (mysqli_next_result($this->conexion));
        } else {
            return array('errorMsg' => 'No ha sido posible obtener el id de sesión del usuario: ' . mysqli_error($this->conexion));
        }

        //mysqli_close($this->conexion); No cerremos la conexion para reusarla    

        return $result_row[0];
     }

    public function closeLogin($values)
    {
        //@diegotorres50: metodo que cierra la sesion del usuario en la base de datos, esto es en un procedimiento de mysql
        //
        
        if(!isset($values) || empty($values) || !is_array($values)) 
            return array('errorMsg' => 'Se esperaba un objeto como parámetro del método');

        //Ejecutamos el procedimiento
        //No usemos el nombre de la base de datos para evitar errores: nombrebasededatos. procedure(
        $sql = "call procedure_closeLogin(" . $values['login_id'] . ");";

        $result = mysqli_query($this->conexion, $sql);

        if(!$result) {
            return array('errorMsg' => 'No ha sido posible cerrar la sesión del usuario: ' . mysqli_error($this->conexion));
        }

        //mysqli_close($this->conexion); No cerremos la conexion para reusarla    

        return $result;
     }

    /*

    @diegotorres50: estos son metodos dummy
     public function dameAlimentos()
     {
         $sql = "select * from alimentos order by energia desc";

         $result = mysql_query($sql, $this->conexion);

         $alimentos = array();
         while ($row = mysql_fetch_assoc($result))
         {
             $alimentos[] = $row;
         }

         return $alimentos;
     }

     public function buscarAlimentosPorNombre($nombre)
     {
         $nombre = htmlspecialchars($nombre);

         $sql = "select * from alimentos where nombre like '" . $nombre . "' order
 by energia desc";

         $result = mysql_query($sql, $this->conexion);

         $alimentos = array();
         while ($row = mysql_fetch_assoc($result))
         {
             $alimentos[] = $row;
         }

         return $alimentos;
     }

     public function dameAlimento($id)
     {
         $id = htmlspecialchars($id);

         $sql = "select * from alimentos where id=".$id;

         $result = mysql_query($sql, $this->conexion);

         $alimentos = array();
         $row = mysql_fetch_assoc($result);

         return $row;

     }

     public function insertarAlimento($n, $e, $p, $hc, $f, $g)
     {
         $n = htmlspecialchars($n);
         $e = htmlspecialchars($e);
         $p = htmlspecialchars($p);
         $hc = htmlspecialchars($hc);
         $f = htmlspecialchars($f);
         $g = htmlspecialchars($g);

         $sql = "insert into alimentos (nombre, energia, proteina, hidratocarbono,
 fibra, grasatotal) values ('" .
                 $n . "'," . $e . "," . $p . "," . $hc . "," . $f . "," . $g . ")";

         $result = mysql_query($sql, $this->conexion);

         return $result;
     }
     */
 }