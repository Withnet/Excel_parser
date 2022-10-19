<?php
require_once 'vendor/autoload.php';
require_once 'utilities.php';

$rows = [];
$uniq_array = [];
$len = 3;
$arrayByMonth = [];
?>
    <head>
        <title>Отчет о звонках</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet"
              integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x"
              crossorigin="anonymous">
        <style>
            body {
                margin-top: 20px;
                margin-left: 20px;
            }
        </style>
    </head>
    <body>
    <form enctype="multipart/form-data" method="post" action="index.php">
        <div class="mb-3">
            <input type="file" name="file" required>
        </div>
        <div class="mb-3">
            <input type="text" name="len">
            <div id="emailHelp" class="form-text">За количество месяцев (3 по умолчанию)</div>
        </div>
        <input class="btn btn-danger btn-sm" type="submit" name="submit" value="Получить данные">
    </form>

    <?php
    if (isset($_POST['submit'])) {
        $spreadsheet = PhpOffice\PhpSpreadsheet\IOFactory::load($_FILES['file']['tmp_name']);
        $rows = $spreadsheet->getActiveSheet()->toArray();
        $rows = array_reverse($rows);
        $array = [];


        $len = isset($_POST['len']) && ((int)$_POST['len'] > 0 && (int)$_POST['len'] != 3) ? (int)$_POST['len'] : $len;


        foreach ($rows as $key => $value) {
            if ($value[0] == 'входящий') {
                $explodeDate = explode('/', $value[5]);
                $array[$key] = array('phone' => $value[1], 'month' => $explodeDate[0], 'year' => $explodeDate[2]);
            }
        }

        $array = array_unique_key($array, 'phone');
        $array = array_values($array);

        $lastMonth = $array[count($array) - 1]['month'];

        foreach ($array as $key => $value) {
            if ((int)$value['month'] > (int)$lastMonth - $len) {
                $uniq_array[] = $value['phone'];
            }
        }

        for ($i = (int)$lastMonth; $i > (int)$lastMonth - $len; $i--) {
            $count = 0;
            foreach ($array as $key => $value) {
                if ((int)$value['month'] == $i) {
                    $count += 1;
                }
            }
            $arrayByMonth[$i] = $count;
        }
    }
    ?>

    <?php if ($uniq_array): ?>
    <div style="width: 350px;">
        <table class="table table-danger">
            <tr class="table-danger">
                <th class="table-danger">Всего за период:</th>
                <td class="table-danger"><?= count($uniq_array); ?></td>
            </tr>
            <?php if (count($arrayByMonth) > 1): ?>
                <?php foreach ($arrayByMonth as $key => $value): ?>
                    <tr>
                        <th class="table-danger">За <?= get_month()[$key] ?>:</th>
                        <td class="table-danger"><?= $value; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </table>
    </div>
    <?php endif; ?>
    </body>

<?php
