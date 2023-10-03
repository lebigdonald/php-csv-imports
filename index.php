<?php

require_once "vendor/autoload.php";

$client = new GuzzleHttp\Client();

function trimOver(string $subject  = null, $search = null): string
{
    if ($subject !== null) {
        if ($search !== null) {
            return str_replace($search, '', $subject);
        }
        return str_replace(['(', ')'], '', $subject);
    }
}

if (isset($_POST["import_client"])) {
    $filename = $_FILES['file']['tmp_name'];
    if ($_FILES['file']['size'] > 0) {
        $file = fopen($filename, "r");
        $line = 0;
        $result = [];
        
        $data = [];
        fgetcsv($file);

        while (($getData = fgetcsv($file, 0, ";"))) {
            try {
                if ($getData[0] !== '') {
                    $dob = $getData[3];
                    $issuedate = $getData[10];
                    if ($dob === '') {
                         $dob = date("Y-m-d");
                    }
                    if ($issuedate === '') {
                        $issuedate = date("Y-m-d");
                    }
                    
                    $params = [
                        'idinstitute' => '',
                        'book_no' => $getData[0],
                        'name' => strtoupper($getData[1]),
                        'surname' => strtoupper($getData[2]),
                        'dob' => date("Y-m-d", strtotime($dob)),
                        'pob' => strtoupper($getData[4]),
                        'status' => strtoupper($getData[5]),
                        'gender' => strtoupper($getData[6]),
                        'profession' => $getData[7],
                        'nic_type' => strtoupper($getData[8]),
                        'nic' => $getData[9],
                        'issuedate' => date("Y-m-d", strtotime($issuedate)),
                        'issueplace' => strtoupper(trimOver($getData[11], ' ')),
                        'phone1' => trimOver($getData[12], ' '),
                        'phone2' => trimOver($getData[13], ' '),
                        'email' => $getData[14],
                        'country' => $getData[15],
                        'region' => $getData[16],
                        'division' => $getData[17],
                        'subdiv' => $getData[18],
                        'town' => $getData[19],
                        'address' => strtoupper($getData[20]),
                        'quarter' => strtoupper($getData[21]),
                        'sms_lang' => $getData[22],
                        'collector' =>  $getData[23],
                        'lang' => $getData[24]
                    ];
                    
                    $filename_2 = $_FILES['file_2']['tmp_name'];
                    if ($_FILES['file_2']['size'] > 0) {
                        $file_2 = fopen($filename_2, "r");
    
                        $bene_name = [];
                        $bene_relate = [];
                        $bene_phone = [];
                        $bene_ratio = [];
                        
                        fgetcsv($file_2);

                        while (($getData_2 = fgetcsv($file_2, 0, ";"))) {
                            if ($getData[0] === $getData_2[4]) {
                                $bene_name[] = strtoupper($getData_2[0]);
                                $bene_relate[] = strtoupper($getData_2[1]);
                                $bene_phone[] = trimOver($getData_2[2], ' ');
                                $bene_ratio[] = $getData_2[3];
                            }
                        }
    
                        $params['bene_name'] = $bene_name;
                        $params['bene_relate'] = $bene_relate;
                        $params['bene_phone'] = $bene_phone;
                        $params['bene_ratio'] = $bene_ratio;
                    }
                    
                    //$data[] = $params;
                    //var_dump($params);
                    //die();
                    
                    try {
                        $response = $client->request("POST", "http://omegamfi.com/oc_classic/registration/import_clients",
                            [
                                'headers' => [
                                    'Accept' => '*',
                                    'Content-Type' => 'multipart/form-data; boundary=----WebKitFormBoundaryZA3hPvTduWMYE1u3',
                                    'Host' => 'omegamfi.com',
                                    'Origin' => null
                                ],
                                "query" => $params
                            ]);
                        if ($response->getStatusCode() === 200) {
                            $result[] = (string) $response->getBody();
                            ++$line;
                        }
                    } catch (\Exception $ex) {
                        var_dump($ex);
                        die();
                    }
                }
            } catch (Exception $ex) {
                die($ex);
            }
        }
        //var_dump($data);
        //die();
        fclose($file);
        var_dump($result);
        echo "<hr /><p><h1>$line  Customers Imported</h1> <br /></p>";
    } else {
        echo "Please choose a file";
        exit();
    }
}

if (isset($_POST["import_client_balance"])) {
    $filename = $_FILES['file']['tmp_name'];
    if ($_FILES['file']['size'] > 0) {
        $file = fopen($filename, "r");
        $line = 0;
        $result = [];
        
        $data = [];
        fgetcsv($file);

        while (($getData = fgetcsv($file, 0, ";"))) {
            try {
                if ($getData[0] !== '') {
                    if ((int)trimOver($getData[1], ' ') !== 0) {
                        $params = [
                            'book_no' => $getData[0],
                            'amount' => trimOver($getData[1], ' '),
                            'collector' => $getData[2]
                        ];
                        
                        //$data[] = $params;
                        //var_dump($params);
                        //die();
                        
                        try {
                            $response = $client->request("POST", "http://omegamfi.com/oc_classic/cash_in/import_client_balance",
                                [
                                    'headers' => [
                                        'Accept' => '*',
                                        'Content-Type' => 'multipart/form-data; boundary=----WebKitFormBoundaryZA3hPvTduWMYE1u3',
                                        'Host' => 'omegamfi.com',
                                        'Origin' => null
                                    ],
                                    "query" => $params
                                ]);
                            if ($response->getStatusCode() === 200) {
                                $result[] = (string) $response->getBody();
                                ++$line;
                            }
                        } catch (\Exception $ex) {
                            var_dump($ex);
                            die();
                        }
                    }
                }
            } catch (Exception $ex) {
                die($ex);
            }
        }
        //var_dump($data);
        //die();
        fclose($file);
        var_dump($result);
        echo "<hr /><p><h1>$line Clients Balance Imported</h1> <br /></p>";
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
            Fichier Client : <input type="file" name="file" id="file" required/> <br>
            Fichier Ayant Droit Client : <input type="file" name="file_2" id="file_2"/>
            <input type="submit" name="import_client" class="button" value="Import">
        </form>
    </div>
</div>

<div class="login-page">
    <div class="form">
        <form class="login-form" method="POST" action="" enctype="multipart/form-data">
            Fichier Solde Client : <input type="file" name="file" id="file" required/> <br>
            <input type="submit" name="import_client_balance" class="button" value="Import">
        </form>
    </div>
</div>
</body>
</html>
