const io = require('socket.io')(2085, {
    cors: {
        origin: '*',
        methods: ['GET', 'POST']
    }
});
const Redis = require('ioredis');
const redis = new Redis();

io.on('connection', socket => {
    console.log('x100: user connected');
});

redis.psubscribe('*', (err, count) => {});

redis.on('pmessage', (pattern, channel, message) => {
    if (channel === 'updateX100Bet' || channel === 'x100Bet' || channel === 'laravel_database_updateX100Bet') {
        io.emit(channel, message);
    }
}); 