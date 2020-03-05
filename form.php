<?php

if (!empty($_POST)) {

    ignore_user_abort(true); // Продолжать выполнять скрипт даже если пользователь его остановил. Для корректного завершения и удаления

    $zip = new ZipArchive();
    $file_name = tempnam(sys_get_temp_dir(), null);

    $res = $zip->open($file_name, ZipArchive::CREATE);
    if ($res === true) {
        foreach ($results->getFiles() as $file) {
            if (Arr::get($file->id, $_POST, false)) {
                $file_content = $service->files->get($file->getId(), array('alt' => 'media'))->getBody()->getContents();
                $zip->addFromString($file->getName(), $file_content);
            }
        }
        unset($_POST);
        $zip->setArchiveComment("Downloaded from '" . URL::get(true) . "'");
        $zip->close();
        header('Pragma: public');
        header('Expires: 0');
        header('Content-Type: application/zip');
        header("Content-Transfer-Encoding: Binary");
        header('Content-Disposition: attachment; filename="Downloaded_files.zip"');
        header('Cache-Control: must-revalidate');
        header('Content-Length: ' . filesize($file_name));

        fpassthru(fopen($file_name, 'rb'));

        unlink($file_name);
    }

}
?>
<form action="/" method="post">
    <table border="1">
        <tr>
            <th><INPUT type="checkbox" onchange="checkAll(this)" name="chk[]"/></th>
            <th>Stats (clickable)</th>
            <th>Thumbnail</th>
        </tr>
        <?php
        foreach ($results->getFiles() as $file) {
            $createdTime = new DateTime($file->createdTime);
            ?>
            <tr>
                <td><input type="checkbox" id="<?= $file->getID(); ?>" name="<?= $file->getID(); ?>"></td>
                <td>
                    <label for="<?= $file->getID(); ?>">
                        Name: <?= $file->getName(); ?>
                        <br>
                        Size: <?= Helper::human_filesize($file->size); ?>
                        <br>
                        Created at: <?= $createdTime->format('d.m.Y h:i:s'); ?> GMT
                    </label>
                </td>
                <td><label for="<?= $file->getID(); ?>"><img src="<?= $file->thumbnailLink; ?>"/></label></td>
            </tr>
            <?php
        }
        ?>
    </table>
    <button type="submit">
        Скачать выбранные файлы
    </button>
</form>

<style>
    table tr * {
        text-align: center;
        vertical-align: center;
        padding: 5px;
    }

    table img {
        max-width: 50px;
        max-height: 50px;
    }

    button {
        margin-top: 10px;
    }
</style>

<script>
    function checkAll(ele) {
        var checkboxes = document.getElementsByTagName('input');
        for (var i = 0; i < checkboxes.length; i++) {
            if (checkboxes[i].type == 'checkbox') {
                checkboxes[i].checked = ele.checked;
            }
        }
    }
</script>
