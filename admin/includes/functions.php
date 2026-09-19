<?php

/*
|--------------------------------------------------------------------------
| Session
|--------------------------------------------------------------------------
*/

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/*
|--------------------------------------------------------------------------
| CSRF
|--------------------------------------------------------------------------
*/

function generate_csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}


function validate_csrf_token(string $token): bool
{
    return isset($_SESSION['csrf_token']) &&
        hash_equals($_SESSION['csrf_token'], $token);
}


/*
|--------------------------------------------------------------------------
| Local Ollama AI
|--------------------------------------------------------------------------
|
| Ollama runs locally on:
|
| http://127.0.0.1:11434
|
| Model:
|
| qwen2.5:3b
|
*/

function get_ollama_base_url(): string
{
    $baseUrl = getenv('OLLAMA_BASE_URL') ?: getenv('OLLAMA_HOST') ?: 'http://127.0.0.1:11434';

    return rtrim($baseUrl, '/');
}

function get_ollama_model(): string
{
    return getenv('OLLAMA_MODEL') ?: 'qwen2.5:3b';
}

function ask_local_ai(
    string $userMessage,
    string $databaseContext = ''
): string {

    /*
    |--------------------------------------------------------------------------
    | AI Instructions
    |--------------------------------------------------------------------------
    */

    $systemPrompt = <<<PROMPT
You are SmartStock AI Assistant.

SmartStock is a university inventory and ordering system.

You help users with:

- Products
- Product prices
- Stock availability
- Inventory
- Orders
- Customer support
- General SmartStock questions

Be friendly, professional and concise.

IMPORTANT RULES:

1. Never invent product information.
2. Never invent stock quantities.
3. Never invent prices.
4. Use the database information below as the source of truth.
5. If requested information is not available in the database information, clearly say that you do not have that information.
6. Do not expose passwords.
7. Do not expose API keys.
8. Do not expose database credentials.
9. Do not expose internal system information.
10. If the user asks something unrelated to SmartStock, politely explain that you mainly help with SmartStock.

DATABASE INFORMATION:

$databaseContext
PROMPT;


    /*
    |--------------------------------------------------------------------------
    | Ollama Request
    |--------------------------------------------------------------------------
    */

    $payload = [
        'model' => get_ollama_model(),
        'system' => $systemPrompt,
        'prompt' => $userMessage,
        'stream' => false,
    ];

    if (!function_exists('curl_init')) {
        error_log('Ollama requested but PHP cURL is not available.');
        return 'The local AI service is not available because PHP cURL is missing.';
    }

    $ollamaUrl = get_ollama_base_url() . '/api/generate';

    /*
    |--------------------------------------------------------------------------
    | Connect to Ollama
    |--------------------------------------------------------------------------
    */

    $ch = curl_init($ollamaUrl);

    $jsonPayload = json_encode($payload);
    if ($jsonPayload === false) {
        return 'The local AI service could not prepare the request.';
    }

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json',
        ],
        CURLOPT_POSTFIELDS => $jsonPayload,
        CURLOPT_TIMEOUT => 120,
        CURLOPT_CONNECTTIMEOUT => 10,
    ]);

    /*
    |--------------------------------------------------------------------------
    | Execute request
    |--------------------------------------------------------------------------
    */

    $response = curl_exec($ch);

    /*
    |--------------------------------------------------------------------------
    | Connection Error
    |--------------------------------------------------------------------------
    */

    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);

        error_log('Ollama cURL Error: ' . $error);

        return 'I could not connect to the local AI service. Please make sure Ollama is running on ' . get_ollama_base_url() . '.';
    }

    /*
    |--------------------------------------------------------------------------
    | HTTP Status
    |--------------------------------------------------------------------------
    */

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    /*
    |--------------------------------------------------------------------------
    | Ollama Error
    |--------------------------------------------------------------------------
    */

    if ($httpCode >= 400) {
        error_log('Ollama HTTP Error: ' . $response);
        return 'The local AI service returned an error. Please try again.';
    }

    /*
    |--------------------------------------------------------------------------
    | Decode JSON
    |--------------------------------------------------------------------------
    */

    $data = json_decode($response, true);
    if (!is_array($data)) {
        error_log('Ollama invalid JSON: ' . $response);
        return 'The local AI service returned an invalid response.';
    }

    if (isset($data['error']) && is_string($data['error']) && $data['error'] !== '') {
        error_log('Ollama API Error: ' . $data['error']);
        return 'The local AI service is unavailable right now. Please check the Ollama model or server.';
    }

    /*
    |--------------------------------------------------------------------------
    | Get AI Response
    |--------------------------------------------------------------------------
    */

    if (isset($data['response']) && is_string($data['response'])) {
        $reply = trim($data['response']);

        return $reply !== '' ? $reply : 'I could not generate a response. Please try again.';
    }

    /*
    |--------------------------------------------------------------------------
    | No Response
    |--------------------------------------------------------------------------
    */

    return 'I could not generate a response. Please try again.';
}


/*
|--------------------------------------------------------------------------
| SmartStock Chatbot
|--------------------------------------------------------------------------
*/

function get_chatbot_reply(
    $conn,
    string $message
): string {

    /*
    |--------------------------------------------------------------------------
    | Clean User Message
    |--------------------------------------------------------------------------
    */

    $message = trim($message);


    /*
    |--------------------------------------------------------------------------
    | Empty Message
    |--------------------------------------------------------------------------
    */

    if ($message === '') {

        return 'Hello! I can help you with products, stock, orders and support. What would you like to know?';
    }


    /*
    |--------------------------------------------------------------------------
    | Normalize Message
    |--------------------------------------------------------------------------
    */

    $normalized = strtolower(
        $message
    );


    /*
    |--------------------------------------------------------------------------
    | Database Context
    |--------------------------------------------------------------------------
    */

    $databaseContext = '';


    /*
    |--------------------------------------------------------------------------
    | Database Available
    |--------------------------------------------------------------------------
    */

    if ($conn) {


        /*
        |--------------------------------------------------------------------------
        | Get Active Products
        |--------------------------------------------------------------------------
        */

        $stmt = $conn->prepare(
            "SELECT title, price, stock_quantity
             FROM tbl_product
             WHERE active = 'Yes'
             ORDER BY created_at DESC
             LIMIT 10"
        );


        if ($stmt) {

            $stmt->execute();

            $result = $stmt->get_result();


            if ($result) {

                $databaseContext .=
                    "ACTIVE PRODUCTS:\n";


                while (
                    $row = $result->fetch_assoc()
                ) {

                    $title =
                        $row['title'];


                    $price =
                        number_format(
                            (float)$row['price'],
                            2
                        );


                    $stock =
                        (int)$row['stock_quantity'];


                    $databaseContext .=
                        "- {$title} | Price: ৳{$price} | Stock: {$stock}\n";
                }
            }


            $stmt->close();
        }


        /*
        |--------------------------------------------------------------------------
        | Search Products Based On User Question
        |--------------------------------------------------------------------------
        */

        $searchTerm = trim(

            preg_replace(

                '/\b(stock|available|inventory|in stock|product|item|price|cost|buy|show|tell|me|about)\b/i',

                ' ',

                $message
            )
        );


        /*
        |--------------------------------------------------------------------------
        | Clean Search Term
        |--------------------------------------------------------------------------
        */

        $searchTerm = trim(

            preg_replace(
                '/\s+/',
                ' ',
                $searchTerm
            )
        );


        /*
        |--------------------------------------------------------------------------
        | Product Search
        |--------------------------------------------------------------------------
        */

        if ($searchTerm !== '') {

            $like =
                '%' . $searchTerm . '%';


            $stmt = $conn->prepare(

                "SELECT
                    title,
                    description,
                    price,
                    stock_quantity
                 FROM tbl_product
                 WHERE active = 'Yes'
                 AND (
                    title LIKE ?
                    OR description LIKE ?
                 )
                 LIMIT 5"

            );


            if ($stmt) {

                $stmt->bind_param(
                    'ss',
                    $like,
                    $like
                );


                $stmt->execute();


                $result =
                    $stmt->get_result();


                if (
                    $result &&
                    $result->num_rows > 0
                ) {

                    $databaseContext .=
                        "\nPRODUCTS MATCHING USER QUERY:\n";


                    while (
                        $row =
                        $result->fetch_assoc()
                    ) {

                        $title =
                            $row['title'];


                        $description =
                            strip_tags(
                                (string)$row['description']
                            );


                        $price =
                            number_format(
                                (float)$row['price'],
                                2
                            );


                        $stock =
                            (int)$row['stock_quantity'];


                        $databaseContext .=

                            "- {$title} | " .
                            "Price: ৳{$price} | " .
                            "Stock: {$stock} | " .
                            "Description: {$description}\n";
                    }
                }


                $stmt->close();
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Send User Question To Local AI
    |--------------------------------------------------------------------------
    */

    return ask_local_ai(
        $message,
        $databaseContext
    );
}