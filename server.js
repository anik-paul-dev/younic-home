import express from 'express';
import { createServer } from 'http';
import { Server } from 'socket.io';
import cors from 'cors';

const app = express();
const httpServer = createServer(app);

const io = new Server(httpServer, {
    cors: {
        origin: "*", // allow all for test
        methods: ["GET", "POST"]
    }
});

app.use(cors());
app.use(express.json());

// Endpoint for Laravel to emit events to Socket.io
app.post('/emit', (req, res) => {
    const { event, data } = req.body;
    
    if (event && data) {
        if (event === 'user-notification' && data.user_id) {
            // Emit to specific user channel
            io.emit(`user-notification:${data.user_id}`, data);
        } else {
            // General broadcast
            io.emit(event, data);
        }
        return res.json({ success: true });
    }
    
    return res.status(400).json({ error: 'Invalid payload' });
});

io.on('connection', (socket) => {
    console.log(`Client connected: ${socket.id}`);
    
    socket.on('disconnect', () => {
        console.log(`Client disconnected: ${socket.id}`);
    });
});

const PORT = process.env.SOCKET_IO_PORT || 6001;

httpServer.listen(PORT, () => {
    console.log(`Younic Home Realtime Server running on port ${PORT}`);
});
