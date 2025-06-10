<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Real-time Chat</title>
</head>
<body>
    <h1>Chat Realtime Test</h1>

    <ul id="messages"></ul>

    <script src="https://cdn.jsdelivr.net/npm/laravel-echo/dist/echo.iife.js"></script>
    <script src="https://cdn.socket.io/4.7.5/socket.io.min.js"></script>

    <script>
        const userId = {{ auth()->id() }}; // pastikan user login

        const echo = new Echo({
            broadcaster: 'reverb',
            key: '{{ env('REVERB_APP_KEY') }}',
            host: '{{ env('REVERB_HOST') }}:{{ env('REVERB_PORT') }}',
            client: io,
        });

        echo.private(`chat.${userId}`)
            .listen('MessageSent', (e) => {
                const li = document.createElement('li');
                li.textContent = `[${e.message.sender_id} ➝ ${e.message.receiver_id}]: ${e.message.content}`;
                document.getElementById('messages').appendChild(li);
            });

        console.log('Listening for chat...');
    </script>
</body>
</html>
