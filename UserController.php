<?php

require './setup.php';

$action = "";
$_POST = json_decode(file_get_contents("php://input"),true);

if (array_key_exists('action', $_POST))
{
    $action = $_POST['action'];
}

$conn = new mysqli(SERVER, USERNAME, PASSWORD, 'wecrypt');

if ($conn->connect_error)
{
    $error = 
    [
        'status' => 
        [
            'code' => 500,
            'message' => 'Problem while connecting to the database'
        ],
        "data" => []
    ];
    die(json_encode($error));
}

$result = [
    "status" => [ 
        'code' => 404,
        'message' => 'Action not found'
    ],
    "data" => []
];

if ($action == "login")
{
    $username = $_POST['username'];
    $password = encrypt($_POST['password']);

    if (!$username || !$password) {
        $error = 
        [
            'status' => 
            [
                'code' => 405,
                'message' => 'Missing data'
            ],
            "data" => []
        ];
        die(json_encode($error));
    }

    $sql = "SELECT * FROM user WHERE username = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();

    $data = $stmt->get_result();
    $count = 0;
    $user_id = 0;
    // $pass = "";
    while($row = $data->fetch_assoc()) {
        $count++;
        $user_id = $row['id'];
        // $pass = $row['password'];
    }

    $stmt->close();

    if ($count == 1 && $user_id != 0) {
        // error_log($pass);
        error_log("success");
        // die;
        $result = [
            "status" => [ 
                'code' => 200,
                'message' => 'User found'
            ],
            "data" => [
                "username" => $username,
                "id" => $user_id
            ]
        ];
    } else {
        $result = [
            "status" => [ 
                'code' => 405,
                'message' => 'Username or password is incorrect'
            ],
            "data" => []
        ];
    }

}
else if ($action == "register")
{
    $username = $_POST['username'];
    $password = encrypt($_POST['password']);
    $repassword = encrypt($_POST['repassword']);

    if (!$username || !$password || !$repassword) {
        $error = 
        [
            'status' => 
            [
                'code' => 405,
                'message' => 'Missing data'
            ],
            "data" => []
        ];
        die(json_encode($error));
    }

    $sql = "SELECT * FROM user WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();

    $data = $stmt->get_result();
    $count = 0;
    while($row = $data->fetch_assoc()) {
        $count++;
    }

    if ($count > 0) {
        $error = 
        [
            'status' => 
            [
                'code' => 405,
                'message' => 'Username is taken'
            ],
            "data" => []
        ];
        die(json_encode($error));
    }

    if ($password != $repassword)
    {
        $error = 
        [
            'status' => 
            [
                'code' => 405,
                'message' => 'Passwords must match'
            ],
            "data" => []
        ];
        die(json_encode($error));
    }

    $sql = "INSERT INTO user (username, password) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();

    $stmt->close();

    $result = [
        "status" => [ 
            'code' => 200,
            'message' => 'User created'
        ],
        "data" => []
    ];
}

echo(json_encode($result));