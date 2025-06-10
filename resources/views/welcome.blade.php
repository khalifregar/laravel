<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome with Chat</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <h1>Selamat Datang di Propedia 👋</h1>

    @if (session('error'))
        <p style="color: red">{{ session('error') }}</p>
    @endif

    @guest
        <h2>Register</h2>
        <form method="POST" action="{{ url('/web-register') }}">
            @csrf
            <input type="email" name="email" placeholder="Email" required><br>
            <input type="password" name="password" placeholder="Password" required><br>
            <select name="role" required>
                <option value="">-- Pilih Role --</option>
                <option value="penjual">Penjual</option>
                <option value="pembeli">Pembeli</option>
            </select><br>
            <button type="submit">Register</button>
        </form>

        <h2>Login</h2>
        <form method="POST" action="{{ url('/web-login') }}">
            @csrf
            <input type="email" name="email" placeholder="Email" required><br>
            <input type="password" name="password" placeholder="Password" required><br>
            <button type="submit">Login</button>
        </form>
    @else
        <p>Login sebagai: <strong>{{ auth()->user()->email }}</strong> (Role: {{ auth()->user()->role }})</p>

        <form method="POST" action="{{ url('/logout') }}">
            @csrf
            <button type="submit">Logout</button>
        </form>

        <p>Real-time chat listener aktif di bawah ini:</p>
        <ul id="messages"></ul>

        <!-- Laravel Echo -->
        <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.0/dist/echo.iife.js"></script>

        <!-- ReverbConnector -->
        <script>
            class ReverbConnector {
                constructor(options) {
                    const scheme = options.scheme || 'ws';
                    const host = options.host || '127.0.0.1:8080';
                    const appKey = options.key || '{{ env('REVERB_APP_KEY') }}';
                    const wsUrl = `${scheme}://${host}/app/${appKey}`;

                    this.socket = new WebSocket(wsUrl);
                    this.callbacks = {};

                    this.socket.addEventListener('open', () => {
                        console.log('✅ WebSocket terhubung');
                    });

                    this.socket.addEventListener('message', event => {
                        try {
                            const data = JSON.parse(event.data);
                            if (data?.event && this.callbacks[data.event]) {
                                this.callbacks[data.event](data.data);
                            }
                        } catch (e) {
                            console.error('❌ Gagal parse message:', e);
                        }
                    });

                    this.socket.addEventListener('error', (e) => {
                        console.error('❌ Koneksi WebSocket gagal:', e);
                    });
                }

                connect() {
                    return this.socket;
                }

                socketId() {
                    return null;
                }

                listen(event, callback) {
                    this.callbacks[event] = callback;
                }

                privateChannel(name) {
                    return {
                        listen: (event, callback) => {
                            this.listen(`${name}.${event}`, callback);
                            return this;
                        }
                    };
                }

                channel(name) {
                    return this.privateChannel(name);
                }

                disconnect() {
                    this.socket?.close();
                }
            }

            window.ReverbConnector = ReverbConnector;
        </script>

        <!-- Echo Init -->
        <script>
            const userId = {{ auth()->id() }};
            console.log("👀 Listening private chat channel untuk user ID:", userId);

            window.Echo = new Echo({
                broadcaster: ReverbConnector,
                host: '127.0.0.1:8080',
                scheme: 'ws',
                key: '{{ env('REVERB_APP_KEY') }}',
            });

            window.Echo.private(`chat.${userId}`)
                .listen('MessageSent', (e) => {
                    console.log('📨 Pesan masuk:', e);
                    const li = document.createElement('li');
                    li.textContent = `[${e.sender_id} ➝ ${e.receiver_id}]: ${e.content}`;
                    document.getElementById('messages').appendChild(li);
                });
        </script>
    @endguest
</body>
</html>
