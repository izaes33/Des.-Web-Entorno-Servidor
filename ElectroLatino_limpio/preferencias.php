
<?php


require_once __DIR__ . '/includes/config.php';


if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {


    exigirPostValido();


    $accion = $_POST['accion'] ?? 'guardar';


    $opciones = [


        'expires' => time() + 30 * 24 * 60 * 60,


        'path' => '/',


        'secure' => $https,


        'httponly' => true,


        'samesite' => 'Lax',

    ];


    if ($accion === 'borrar') {


        $opciones['expires'] = time() - 3600;


        setcookie('tema', '', $opciones);


        setcookie('visitante', '', $opciones);


    } else {


        $tema = $_POST['tema'] ?? 'claro';


$tema = is_string($tema) &&
        in_array($tema, ['claro', 'oscuro'], true)
        ? $tema : 'claro';


setcookie('tema', $tema, $opciones);


$nombre = $_POST['visitante'] ?? '';


$nombre = is_string($nombre) ? trim($nombre) : '';


$nombre = preg_replace(
    '/[\x00-\x1F\x7F]/u',
    '',
    $nombre
) ?? '';


$nombre = substr($nombre, 0, 40);


if ($nombre !== '') {


    setcookie('visitante', $nombre, $opciones);

} else {


    $opciones['expires'] = time() - 3600;


    setcookie('visitante', '', $opciones);
}


    }
    volver('preferencias.php');

}


cabecera('Preferencias');


$visitante = $_COOKIE['visitante'] ?? '';
$visitante = is_string($visitante) ? substr($visitante,0,40) : '';

?>


<section class="intro">

    <p class="etiqueta">COOKIES EN EL CLIENTE</p>

    <h1>Preferencias del visitante</h1>

    <p>
        En esta página podrás configurar tus preferencias
        de navegación, como tu nombre y el tema visual.
    </p>

</section>


<div class="panel estrecho">


    <form method="post">


        <?php


        ?>

        <?= campoCsrf() ?>


        <input
            type="hidden"
            name="accion"
            value="guardar"
        >


        <label for="visitante">
            Nombre (opcional)
        </label>


        <input
            type="text"
            id="visitante"
            name="visitante"
            maxlength="40"
            value="<?= e($visitante) ?>"
            placeholder="Ej.: Ana"
        >


        <label for="tema">
            Tema visual
        </label>


        <select id="tema" name="tema">


            <option value="claro" selected>
                Claro
            </option>


            <option value="oscuro">
                Oscuro
            </option>

        </select>


        <button type="submit">
            Guardar preferencias
        </button>

    </form>


    <form method="post" class="separado">


        <?php


        ?>

        <?= campoCsrf() ?>


        <input
            type="hidden"
            name="accion"
            value="borrar"
        >


        <button
            type="submit"
            class="secundario"
        >
            Borrar cookies de preferencias
        </button>

    </form>


    <p class="nota">
        Las preferencias del visitante se almacenarán
        mediante cookies del navegador.
    </p>

</div>


<?php


pie();
