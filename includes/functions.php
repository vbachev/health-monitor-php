<?php

function escapedParamOrNull ( $paramName, $method ) {
    global $db;

    if(!isset($method)){
        $method = $_REQUEST;
    }

    if(!isset($method[ $paramName ])){
        return 'NULL';
    }

    return $db->escape($method[ $paramName ]);
}

function calculateLight ( $light ) {
    $rawRange = 1024; // 3.3v
    $logRange = 5.0; // 3.3v

    return  pow(10, intval($light) * $logRange / $rawRange);
}

function calculateGas ( $ppm ) {
    $MQ135_SCALINGFACTOR = 116.6020682;
    $MQ135_EXPONENT = -2.769034857;
    $resvalue = 100;

    return $resvalue * exp(log($MQ135_SCALINGFACTOR / $ppm) / $MQ135_EXPONENT);
}

// POST
function handlePostRequest () {
    global $db;
    
    if(isset($_POST['positionId'])){

        $normalizedGasValue = isset($_POST['gas']) ? calculateGas($_POST['gas']) : 'NULL';
        $normalizedLightValue = isset($_POST['light']) ? calculateLight($_POST['light']) : 'NULL';

        $q = 'INSERT 
            INTO `metrics` (
                `positionId`, 
                `time`,
                `temp`, 
                `wet`, 
                `gas`, 
                `light`, 
                `noise`
            ) VALUES (
                ' . escapedParamOrNull('positionId', $_POST) . ',
                ' . time() . ',
                ' . escapedParamOrNull('temp', $_POST) . ',
                ' . escapedParamOrNull('wet', $_POST) . ',
                ' . $normalizedGasValue . ',
                ' . $normalizedLightValue . ',
                ' . escapedParamOrNull('noise', $_POST) . '
            )';
        $result = $db->query($q);

        if($result){
            echo json_encode( array( 'message' => 'Post was successful' ));
        } else {
            echo json_encode( array( 'message' => 'Post was not successful' ));
        }
    } else {
        // return error json
        echo json_encode( array( 'message' => 'Post data was incorrect' ));
    }
}

// GET
function handleGetRequest () {
    global $db;
    
    $q = 'SELECT * FROM `metrics`';
    if(isset($_GET['positionId'])){
        $q .= ' WHERE `positionId` = ' . escapedParamOrNull('positionId', $_GET);

        if(isset($_GET['gtetime'])){
            $q .= ' AND `time` >= ' . escapedParamOrNull('gtetime', $_GET);
        }

        if(isset($_GET['lttime'])){
            $q .= ' AND `time` < ' . escapedParamOrNull('lttime', $_GET);
        }    
    }
    $q .= ' ORDER BY `time` DESC';
    $result = $db->query( $q );

    echo json_encode( array(
        'message' => 'Get metrics was successful',
        'query' => $q,
        'result' => $result
    ));
}