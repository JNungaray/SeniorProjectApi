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

if ($action == "getChats") {
    $id = $_POST["user_id"];

    $chats = [];

    $sql = "SELECT u.username, u.id FROM chat c JOIN user u ON c.to_id = u.id WHERE c.from_id = ? GROUP BY c.to_id";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $data = $stmt->get_result();
    while($row = $data->fetch_assoc()) {
        $chats[] = [
            "id" => $row['id'],
            "username" => $row['username']
        ];
    }
    $stmt->close();


    $sql = "SELECT u.username, u.id FROM chat c JOIN user u ON c.from_id = u.id WHERE c.to_id = ? GROUP BY c.from_id";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $data = $stmt->get_result();
    while($row = $data->fetch_assoc()) {
        $chats[] = [
            "id" => $row['id'],
            "username" => $row['username']
        ];
    }
    $stmt->close();

    $chats = array_unique($chats, SORT_REGULAR);

    $result = [
        "status" => [ 
            'code' => 200,
            'message' => 'chat list found'
        ],
        "data" => [
            "chats" => $chats
        ]
    ];
}
else if ($action == "getMessages")
{
    $id = $_POST["user_id"];
    $other_id = $_POST["other_user"];

    $sql = "SELECT c.*, u.username FROM chat c
            JOIN user u ON (u.id = ?)
            WHERE (c.from_id = ? AND c.to_id = ?) OR (c.from_id = ? AND c.to_id = ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiiii", $other_id, $other_id, $id, $id, $other_id);

    $stmt->execute();

    $messages = [];
    $other_username = '';
    $data = $stmt->get_result();
    $count = 0;
    while($row = $data->fetch_assoc()) {
        if ($count == 0) {
            $other_username = $row['username'];
            $count++;
        }
        $messages[] = [
            "from" => $row['from_id'],
            "to" => $row['to_id'],
            "message" => $row['message']
        ];
    }
    $stmt->close();

    $result = [
        "status" => [ 
            'code' => 200,
            'message' => 'messages found'
        ],
        "data" => [
            "username" => $other_username,
            "id" => $other_id,
            "messages" => $messages
        ]
    ];
}
else if ($action == "sendMessage")
{
    $id = $_POST["user_id"];
    $other_id = $_POST["other_user"];
    $message = $_POST["message"];

    $sql = "INSERT INTO chat (message, from_id, to_id) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $message, $id, $other_id);
    $stmt->execute();

    $stmt->close();

    $result = [
        "status" => [ 
            'code' => 200,
            'message' => 'message sent'
        ],
        "data" => []
    ];
}

echo(json_encode($result));