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

    public function getDataFromSingleTable($values)
    {
        //@diegotorres50: metodo que consulta una unica tabla, reusable
        //
        
        if(!isset($values) || empty($values) || !is_array($values)) 
            return array('errorMsg' => 'Se esperaba un objeto como parámetro del método');

        /* Estructura del parametro esperado
        $values['TABLE'] = 'Users';

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

        $values['WHERE'] = 'WHERE user_id = \'diegotorres50\'';

        $values['ORDER_BY'] = array( 
            'user_id desc'
            );

        $values['LIMIT'] = array( 
            'OFFSET' => 0, //Desde la fila
            'ROW_COUNT' => 1 //Cantidad
            );        

        */
       
        $sql = array();
        $sql_rows_total = array();

        //Query para consultas
        $sql[] = "SELECT";
        $sql_rows_total[] = "SELECT COUNT(*) AS TOTAL";

        //Si se especifica paginar la consulta
        if(isset($values['FIELDS']) && !empty($values['FIELDS']) && is_array($values['FIELDS'])) {
            $values['FIELDS'] = implode(",", array_keys($values['FIELDS']));
            $sql[] = $values['FIELDS'];
        } else {
            $sql[] = "*"; //Por defecto trae todos los campos
        }   
        
        $sql[] = "FROM";
        $sql_rows_total[] = "FROM";

        $sql[] = $values['TABLE'];
        $sql_rows_total[] = $values['TABLE'];

        //Si se especifica criterios de consulta
        if(isset($values['WHERE']) && !empty($values['WHERE'])) {
            $sql[] = $values['WHERE'];
            $sql_rows_total[] = $values['WHERE'];
        }        

        //Si se especifica ordenar la consulta
        if(isset($values['ORDER_BY']) && !empty($values['ORDER_BY']) && is_array($values['ORDER_BY'])) {
            $values['ORDER_BY'] = implode(",", $values['ORDER_BY']);
            $sql[] = 'ORDER BY ' . $values['ORDER_BY'];
        } else {
            $sql[] = 'ORDER BY 1 DESC'; //Ordenar por defecto por la primera columna descendente
        }

        //Si se especifica paginar la consulta
        if(isset($values['LIMIT']) && !empty($values['LIMIT']) && is_array($values['LIMIT'])) {
            $sql[] = "LIMIT " . $values['LIMIT']['OFFSET'] . ", " . $values['LIMIT']['ROW_COUNT'];
        }         

        //Armamos la consulta completa con espacios entre los segmentos del query
        $sql = implode(" ", $sql);
        $sql_rows_total = implode(" ", $sql_rows_total);

        $result = mysqli_query($this->conexion, $sql);
        $result_sql_rows_total = mysqli_query($this->conexion, $sql_rows_total);

        if(!$result || !$result_sql_rows_total) {

            return array('errorMsg' => 'No ha sido posible realizar la consulta de ' . $values['TABLE'] . ': ' . mysqli_error($this->conexion));
        }


        // Numeric array
        //$row=mysqli_fetch_array($result,MYSQLI_NUM);
        //printf ("%s (%s)\n",$row[0],$row[1]);

        // Associative array
        //$row=mysqli_fetch_array($result,MYSQLI_ASSOC);
        //printf ("%s (%s)\n",$row["user_id"],$row["user_role"]);

        $rows_found = array();
        while ($row = mysqli_fetch_assoc($result))
        {
           $rows_found[] = $row;
        }

        // Free result set
        mysqli_free_result($result);

        $rows_total=mysqli_fetch_array($result_sql_rows_total,MYSQLI_ASSOC);

        // Free result set
        mysqli_free_result($result_sql_rows_total);        
        
        //mysqli_close($this->conexion); No cerremos la conexion para reusarla    

        $data = array(
            'rows_found' => $rows_found, //Todas las filas
            'total' => $rows_total["TOTAL"] //La cantidad de filas
            );

        return $data;
     }

    public function getDataFromSingleView($values)
    {
        //@diegotorres50: metodo que consulta una unica vista, reusable
        //
        
        if(!isset($values) || empty($values) || !is_array($values)) 
            return array('errorMsg' => 'Se esperaba un objeto como parámetro del método');

        /* Estructura del parametro esperado
        $values['VIEW'] = 'view_users';

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

        $values['WHERE'] = 'WHERE user_id = \'diegotorres50\'';

        $values['ORDER_BY'] = array( 
            'user_id desc'
            );

        $values['LIMIT'] = array( 
            'OFFSET' => 0, //Desde la fila
            'ROW_COUNT' => 1 //Cantidad
            );        

        */
       
        $sql = array();
        $sql_rows_total = array();

        //Query para consultas
        $sql[] = "SELECT";
        $sql_rows_total[] = "SELECT COUNT(*) AS TOTAL";

        //Si se especifica paginar la consulta
        if(isset($values['FIELDS']) && !empty($values['FIELDS']) && is_array($values['FIELDS'])) {
            $values['FIELDS'] = implode(",", array_keys($values['FIELDS']));
            $sql[] = $values['FIELDS'];
        } else {
            $sql[] = "*"; //Por defecto trae todos los campos
        }   
        
        $sql[] = "FROM";
        $sql_rows_total[] = "FROM";

        $sql[] = $values['VIEW'];
        $sql_rows_total[] = $values['VIEW'];

        //Si se especifica criterios de consulta
        if(isset($values['WHERE']) && !empty($values['WHERE'])) {
            $sql[] = $values['WHERE'];
            $sql_rows_total[] = $values['WHERE'];
        }        

        //Si se especifica ordenar la consulta
        if(isset($values['ORDER_BY']) && !empty($values['ORDER_BY']) && is_array($values['ORDER_BY'])) {
            $values['ORDER_BY'] = implode(",", $values['ORDER_BY']);
            $sql[] = 'ORDER BY ' . $values['ORDER_BY'];
        } else {
            $sql[] = 'ORDER BY 1 DESC'; //Ordenar por defecto por la primera columna descendente
        }

        //Si se especifica paginar la consulta
        if(isset($values['LIMIT']) && !empty($values['LIMIT']) && is_array($values['LIMIT'])) {
            $sql[] = "LIMIT " . $values['LIMIT']['OFFSET'] . ", " . $values['LIMIT']['ROW_COUNT'];
        }         

        //Armamos la consulta completa con espacios entre los segmentos del query
        $sql = implode(" ", $sql);
        $sql_rows_total = implode(" ", $sql_rows_total);

        $result = mysqli_query($this->conexion, $sql);
        $result_sql_rows_total = mysqli_query($this->conexion, $sql_rows_total);

        if(!$result || !$result_sql_rows_total) {

            return array('errorMsg' => 'No ha sido posible realizar la consulta de ' . $values['VIEW'] . ': ' . mysqli_error($this->conexion));
        }


        // Numeric array
        //$row=mysqli_fetch_array($result,MYSQLI_NUM);
        //printf ("%s (%s)\n",$row[0],$row[1]);

        // Associative array
        //$row=mysqli_fetch_array($result,MYSQLI_ASSOC);
        //printf ("%s (%s)\n",$row["user_id"],$row["user_role"]);

        $rows_found = array();
        while ($row = mysqli_fetch_assoc($result))
        {
           $rows_found[] = $row;
        }

        // Free result set
        mysqli_free_result($result);

        $rows_total=mysqli_fetch_array($result_sql_rows_total,MYSQLI_ASSOC);

        // Free result set
        mysqli_free_result($result_sql_rows_total);        
        
        //mysqli_close($this->conexion); No cerremos la conexion para reusarla    

        $data = array(
            'rows_found' => $rows_found, //Todas las filas
            'total' => $rows_total["TOTAL"] //La cantidad de filas
            );

        return $data;
     }

    public function getCentersByUser($values)
    {
        //@diegotorres50: metodo que consulta los centros de un usuario
        //
        
        if(!isset($values) || empty($values) || !is_array($values)) 
            return array('errorMsg' => 'Se esperaba un objeto como parámetro del método');
       
        $sql = array();

        //Query para consultas
        $sql[] = "SELECT centers.center_id, centers.center_name";
        $sql[] = "FROM Users, users_x_centers, centers";
        $sql[] = "WHERE";
        $sql[] = "Users.user_id = users_x_centers.users_x_centers_user_id and";
        $sql[] = "users_x_centers.users_x_centers_center_id = centers.center_id and";
        $sql[] = "Users.user_id = '" . $values['USER_ID'] . "' and";
        $sql[] = "centers.center_status = 'ACTIVE' and";
        $sql[] = "Users.user_status = 'ACTIVE'";
        $sql[] = "order by Users.user_id asc, centers.center_id asc";

        //Armamos la consulta completa con espacios entre los segmentos del query
        $sql = implode(" ", $sql);

        $result = mysqli_query($this->conexion, $sql);

        if(!$result) {

            return array('errorMsg' => 'No ha sido posible realizar la consulta de centros de usuario: ' . mysqli_error($this->conexion));
        }


        // Numeric array
        //$row=mysqli_fetch_array($result,MYSQLI_NUM);
        //printf ("%s (%s)\n",$row[0],$row[1]);

        // Associative array
        //$row=mysqli_fetch_array($result,MYSQLI_ASSOC);
        //printf ("%s (%s)\n",$row["user_id"],$row["user_role"]);

        $rows_found = array();

        while ($row = mysqli_fetch_assoc($result))
        {
           $rows_found[] = $row;
        }

        // Free result set
        mysqli_free_result($result);  
        
        //mysqli_close($this->conexion); No cerremos la conexion para reusarla    

        $data = array($rows_found); //Todas las filas

        return $data;
     }

    public function getColumnNames($values)
    {
        //@diegotorres50: metodo que recupera el en un array los nombres de campos de una tabla o vista, esto es en un procedimiento de mysql
        //
        
        if(!isset($values) || empty($values) || !is_array($values)) 
            return array('errorMsg' => 'Se esperaba un objeto como parámetro del método');


        $sql = "set @columnsAlias = '0';"; //Parametro de salida por defecto es cero

        //Escapamos las cadenas de texto
        $values['TABLE'] = mysqli_real_escape_string($this->conexion, $values['TABLE']); //Table puede ser tambien una VIEW de mysql
        $values['database_name'] = mysqli_real_escape_string($this->conexion, $values['database_name']);

        //Ejecutamos el procedimiento
        //No usemos el nombre de la base de datos para evitar errores: nombrebasededatos. procedure(
        $sql .= "call procedure_getColumnNames('" . $values['TABLE'] . "', '" . $values['database_name'] . "', @columnsAlias);";

        $sql .= "select @columnsAlias as _columnsAlias;"; //Recogemos el resultado

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
            return array('errorMsg' => 'No ha sido posible obtener los nombres de las columnas: ' . mysqli_error($this->conexion));
        }

        //mysqli_close($this->conexion); No cerremos la conexion para reusarla    
        //$result_row[0] = explode(',', $result_row[0]); // array(3) { [0]=> string(10) "user_idtmp" [1]=> string(14) "user_statustmp" [2]=> string(12) "user_nametmo" } 

        //var_dump($result_row[0]);
        //exit;

        return explode(',', $result_row[0]); //Devolvemos las columnas en formato array, por defecto el procedure lo retorna como un string de columnas separadas por ',' comas
     }

    public function getRecordsFrom($values)
    {
        //@diegotorres50: metodo que recupera los registros de cualquier tabla o vista, esto es en un procedimiento de mysql
        //
        
        if(!isset($values) || empty($values) || !is_array($values)) 
            return array('errorMsg' => 'Se esperaba un objeto como parámetro del método');

        //Escapamos las cadenas de texto
        $values['TABLE'] = mysqli_real_escape_string($this->conexion, $values['TABLE']); //Table puede ser tambien una VIEW de mysql
        $values['database_name'] = mysqli_real_escape_string($this->conexion, $values['database_name']);
        $values['SEARCH'] = mysqli_real_escape_string($this->conexion, $values['SEARCH']);

        //Si no se especifica ordenar la consulta
        if(!isset($values['ORDER_BY']) || empty($values['ORDER_BY'])) {
            $values['ORDER_BY'] = '1 DESC'; //Ordenar por defecto por la primera columna descendente
        }

        //Si no se especifica limite
        if(!isset($values['OFFSET']) || empty($values['OFFSET'])) {
            $values['OFFSET'] = 0; //Iniciar por defecto por la primera fila
        }

        //Si no se especifica la cantidad de registros por pagina
        if(!isset($values['ROW_COUNT']) || empty($values['ROW_COUNT'])) {
            $values['ROW_COUNT'] = 5; //Por defecto mostrar de a 5
        }        

        //Ejecutamos el procedimiento
        //No usemos el nombre de la base de datos para evitar errores: nombrebasededatos. procedure(
        $sql = "call procedure_findAll('" . $values['TABLE'] . "', '" . $values['database_name'] . "', '" . $values['SEARCH'] . "', '" . $values['ORDER_BY'] . "', " . $values['OFFSET']  . "," . $values['ROW_COUNT'] . ");";

        $sql .= "select * from TMP_" . $values['TABLE'] . ";"; //Recogemos el resultado

        //var_dump($sql); exit;

        /* execute multi query */
        if (mysqli_multi_query($this->conexion, $sql)) {
            do {
                $result_row = array();
                /* store first result set */
                if ($result = mysqli_store_result($this->conexion)) {
                    while ($row = mysqli_fetch_row($result)) {
                        $result_row[] = $row;
                    }
                    mysqli_free_result($result);
                }
                /* print divider */
                //if (mysqli_more_results($this->conexion)) {
                //    printf("ESTEESUNSEPARADOR\n");
                //}
            } while (mysqli_next_result($this->conexion));
        } else {
            return array('errorMsg' => 'No ha sido posible obtener los registros: ' . mysqli_error($this->conexion));
        }

        //mysqli_close($this->conexion); No cerremos la conexion para reusarla    
        /*
        var_dump($result_row);
        exit;

        array(5) { [0]=> array(3) { [0]=> string(5) "user9" [1]=> string(8) "INACTIVE" [2]=> string(0) "" } [1]=> array(3) { [0]=> string(5) "user8" [1]=> string(8) "INACTIVE" [2]=> string(0) "" } [2]=> array(3) { [0]=> string(5) "user7" [1]=> string(8) "INACTIVE" [2]=> string(0) "" } [3]=> array(3) { [0]=> string(5) "user6" [1]=> string(8) "INACTIVE" [2]=> string(0) "" } [4]=> array(3) { [0]=> string(5) "user5" [1]=> string(8) "INACTIVE" [2]=> string(0) "" } } 
        */

        $data = array(
            'rows_found' => $result_row, //Todas las filas
            'total' => $this->getTotalFromTable('COUNT_' . $values['TABLE']) //La cantidad de filas
            );

        return $data; //Devolvemos los registros encontrados y el total de la tabla
     }

    public function getTotalFromTable($tableName)
    {
        //@diegotorres50: metodo que consulta el valor total de registros de una tabla temporal, reusable
        //
       
        $sql = array();

        //Query para consultas
        $sql[] = "SELECT * ";

        $sql[] = "FROM";
 
        $sql[] = $tableName;
 
        //Armamos la consulta completa con espacios entre los segmentos del query
        $sql = implode(" ", $sql);

        $result = mysqli_query($this->conexion, $sql);
 
        $rows_found = array();
 
        while ($row = mysqli_fetch_assoc($result))
        {
           $rows_found[] = $row;
        }

        // Free result set
        mysqli_free_result($result);

        //var_dump($rows_found[0]['total']); exit;

        return $rows_found[0]['total'];
     }

    public function setToPurge($values)
    {

        /*
        @diegotorres50: este procedimiento cambia el estado de purga de registro a '1' de manera
        que el registro se filtre en todas las consultas para ocultarlo y en otro procedimiento
        borrarlo por completo de la tabla
        */
        
        if(!isset($values) || empty($values) || !is_array($values)) 
            return array('errorMsg' => 'Se esperaba un objeto como parámetro del método');

        //Ejecutamos el procedimiento
        //No usemos el nombre de la base de datos para evitar errores: nombrebasededatos. procedure(
        $sql = "call procedure_setToPurge('" . $values['TABLE'] . "', '" . $values['FIELD'] . "', '" . $values['VALUE'] . "');";

        $result = mysqli_query($this->conexion, $sql);

        if(!$result) {
            return array('errorMsg' => 'No ha sido posible setear el estado de purga: ' . mysqli_error($this->conexion));
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