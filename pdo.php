<?php
try {
    $usuario = "usuario";
    $sqlite = new PDO('sqlite:C:\Users\Casa\Desktop\platform_froxa_1.0.1.db');
    $mysql = new PDO('mysql:host=192.168.1.70;dbname=platform_froxa', $usuario,$usuario);
    $mysql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // con esto tengo todos los nombres de las tablas de esta base de datos, solo las tablas, NO! las vistas
    $nombreTabla = $mysql->query("SHOW full TABLES where Table_type='BASE TABLE'");
    while($valores = $nombreTabla->fetch(PDO::FETCH_ASSOC)) {
        //coge el nombre de las columnas de la tabla escogida previamente
        $nombreCampo = $mysql->query("SELECT Column_Name As Nombre_columna FROM Information_Schema.Columns WHERE Table_Name = '" . $valores['Tables_in_platform_froxa'] . "' GROUP BY Column_Name;");
        //esta linea esta añadida porque cuando ya tiene datos esta tabla esta dando errores insesperados
        if (strcmp($valores['Tables_in_platform_froxa'], "datosfiscales") != 0) {
            //realiza consultas en ambas bases de datos
            $datos = $sqlite->query("Select * FROM " . $valores['Tables_in_platform_froxa'] . " ORDER BY id;");
            $comprueba = $mysql->query("SELECT * FROM " . $valores['Tables_in_platform_froxa'] . " ORDER BY ID;");
            while ($dato = $datos->fetch(PDO::FETCH_ASSOC)) {
                $comprobado = $comprueba->fetch(PDO::FETCH_ASSOC);
                $id_dato = $dato['id'];
                $id_compro = $comprobado['id'];
                //comprueba si la existe ya el registro, para no volver a ejecutarlo
                if ($id_dato != $id_compro OR (strcmp($id_dato, $id_compro) != 0)) {
                    do {
                        $nombreCampo = $mysql->query("SELECT Column_Name As Nombre_columna FROM Information_Schema.Columns WHERE Table_Name = '" . $valores['Tables_in_platform_froxa'] . "' GROUP BY Column_Name;");
                        $columna = $nombreCampo->fetch(PDO::FETCH_ASSOC);
                        //añade la primera columna al insert
                        $datos_select = "'" . $dato[$columna['Nombre_columna']] . "'";
                        //añade el primer dato al insert
                        $columnas = $columna['Nombre_columna'];
                        $aux = 1;
                        do {
                            if ($aux != 1) {
                                //añade las columnas al insert
                                $columnas .= "," . $columna['Nombre_columna'];
                                //añade los datos al insert
                                $datos_select .= ", '" . $dato[$columna['Nombre_columna']] . "'";
                            }
                            $aux++;
                        } while ($columna = $nombreCampo->fetch(PDO::FETCH_ASSOC));
                        //si alguno de los campos que vamos a añadir contiene una " ' "(comilla simple) necesitamos cambiarlo puesto que dara error
                        //cambiandolo a un " ` " ya funcionaria correctamente
                        $sql = "INSERT INTO " . $valores['Tables_in_platform_froxa'] . "( " . $columnas . ") VALUES (" . $datos_select . ");\n";
                        //muestra la sql completa
                        print $sql;
                        //ejecuta la sql para añadir los datos en la otra base de datos
                        $mysql->query($sql);
                    } while ($dato = $datos->fetch(PDO::FETCH_ASSOC));
                }
            }
        }
    }
} catch (PDOException $e) {
    print "Error!: " . $e->getMessage() . "\n". $e->getLine();
    die();
}
