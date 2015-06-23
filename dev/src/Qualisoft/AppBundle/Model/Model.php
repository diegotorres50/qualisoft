<?php

namespace Qualisoft\AppBundle\Model;

use Symfony\Component\HttpFoundation\Request;

class Model
{

    /* @diegotorres50: la idea es que sea el modelo de negocio para conectar y gestionar la base de datos */
    protected $conexion;

    public function __construct($dbname,$dbuser,$dbpass,$dbhost)
    {
        $mvc_bd_conexion = mysql_connect($dbhost, $dbuser, $dbpass);

        if (!$mvc_bd_conexion) {
            die('No ha sido posible realizar la conexión con la base de datos: '
              . mysql_error());
        }
         
        mysql_select_db($dbname, $mvc_bd_conexion);

        mysql_set_charset('utf8');

        $this->conexion = $mvc_bd_conexion;
    }

    public function bd_conexion()
    {

    }

    public function setLogin($values)
    {
        //@diegotorres50: metodo que inserta datos de una nueva sesion de usuario
        //
        //
        //$values['login_time'] = $values['login_time']->format('Y-m-d H:i:s');
        //var_dump($values); exit;
        
        if(!isset($values) or empty($values) or !is_array($values)) return null;

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

        $result = mysql_query($sql, $this->conexion);

        if(!$result) {

            return array('errorMsg' => 'No ha sido posible realizar el registro de sesión del usuario: ' . mysql_error());
        }

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