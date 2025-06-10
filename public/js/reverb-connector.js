class ReverbConnector {
    constructor(options) {
        const scheme = options.scheme || 'ws';
        const host = options.host || '127.0.0.1:8080'; // ← Ganti dari localhost ke IP langsung
        const wsUrl = `${scheme}://${host}`;

        this.socket = new WebSocket(wsUrl);

        this.socket.addEventListener('open', () => {
            console.log('✅ WebSocket berhasil terhubung');
        });

        this.socket.addEventListener('error', (e) => {
            console.error('❌ Gagal koneksi WebSocket:', e);
        });
    }

    connect() {
        return this.socket;
    }

    socketId() {
        return null; // native WebSocket gak punya socket ID
    }

    listen(name, callback) {
        this.socket.addEventListener('message', event => {
            try {
                const data = JSON.parse(event.data);
                if (data.event === name) {
                    callback(data.data);
                }
            } catch (e) {
                console.error('❌ Gagal parse:', e);
            }
        });
    }

    channel(name) {
        return this.privateChannel(name);
    }

    privateChannel(name) {
        return {
            listen: (event, callback) => {
                this.listen(`${name}.${event}`, callback);
                return this;
            }
        };
    }

    leave(channel) {
        if (this.socket) this.socket.close();
    }

    disconnect() {
        if (this.socket) this.socket.close();
    }
}

window.ReverbConnector = ReverbConnector;
