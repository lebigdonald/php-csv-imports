<?php
if (isset($_POST["sms_list_status"])) {
    $filename = $_FILES['file_status']['tmp_name'];
    if ($_FILES['file_status']['size'] > 0) {
        $file = fopen($filename, "r");
        $line = 0;
        $result = [];

        $messages = [];
        fgetcsv($file);

        while (($getData = fgetcsv($file, 0, ","))) {
            try {
                if ($getData[0] !== '') {
                    $messages[] = $getData[1];
                    $line++;
                }
            } catch (Exception $ex) {
                die($ex);
            }
        }

        $query = "UPDATE omegamfi_oc_classic.sms SET status = 'N' WHERE branch = 16 AND (DATE(created_at) >= '2024-07-01' AND DATE(created_at) <= '2024-08-31') AND (\n";
        for ($i = 0; $i < count($messages); $i++) {
            $message = $messages[$i];
            $query .= ($i < count($messages) - 1) ? "message = '" . $message . "' OR \n" : "message = '" . $message . "');";
        }

        $filename = 'sms_status_output.txt';

        if (file_put_contents($filename, $query)) {
            echo "<hr /><p><h1>$line  SMS Query Generated</h1> <br /></p>";
        } else {
            echo "<hr /><p><h1>$line  SMS Query Not Generated</h1> <br /></p>";
        }

        fclose($file);
    } else {
        echo "Please choose a file";
        exit();
    }
}

if (isset($_POST["sms_list_member"])) {
    $filename = $_FILES['file_member']['tmp_name'];
    if ($_FILES['file_member']['size'] > 0) {
        $file = fopen($filename, "r");
        $line = 0;
        $result = [];

        $messages = [];
        fgetcsv($file);

        while (($getData = fgetcsv($file, 0, ","))) {
            try {
                if ($getData[0] !== '') {
                    $messages[] = [
                        'idcollect_mem' => $getData[0],
                        'phone1' => $getData[7]
                    ];
                    $line++;
                }
            } catch (Exception $ex) {
                die($ex);
            }
        }

        $query = "";
        for ($i = 0; $i < count($messages); $i++) {
            $message = $messages[$i];
            if ($message['phone1'] !== '') {
                $query .= "UPDATE omegamfi_oc_classic.sms SET member = {$message['idcollect_mem']} WHERE receiver = '237{$message['phone1']}';\n";
            }
        }

        $filename = 'sms_member_output.txt';

        if (file_put_contents($filename, $query)) {
            echo "<hr /><p><h1>$line  SMS Query Generated</h1> <br /></p>";
        } else {
            echo "<hr /><p><h1>$line  SMS Query Not Generated</h1> <br /></p>";
        }

        fclose($file);
    } else {
        echo "Please choose a file";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="eng">
<head>
    <title>O-Collect Imports</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
<div class="login-page">
    <div class="form">
        <form class="login-form" method="POST" action="" enctype="multipart/form-data">
            Fichier des SMS (Status) : <input type="file" name="file_status" id="file_status" required/> <br>
            <input type="submit" name="sms_list_status" class="button" value="Generate Query">
        </form>
    </div>

    <div class="form">
        <form class="login-form" method="POST" action="" enctype="multipart/form-data">
            Fichier des SMS (Member) : <input type="file" name="file_member" id="file_member" required/> <br>
            <input type="submit" name="sms_list_member" class="button" value="Generate Query">
        </form>
    </div>
</div>
</body>
</html>
