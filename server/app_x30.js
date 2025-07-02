const io = require('socket.io')(2084, {
    cors: {
        origin: '*',
        methods: ['GET', 'POST']
    }
});
const Redis = require('ioredis');
const redis = new Redis();

io.on('connection', socket => {
    console.log('x30: user connected');
});

redis.psubscribe('*', (err, count) => {});

redis.on('pmessage', (pattern, channel, message) => {
    if (channel === 'updateWheelBet' || channel === 'wheelBet' || channel === 'laravel_database_updateWheelBet') {
        io.emit(channel, message);
    }
}); 