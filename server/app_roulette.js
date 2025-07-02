const io = require('socket.io')(2090, {
    cors: { origin: '*', methods: ['GET', 'POST'] }
});
const Redis = require('ioredis');
const redis = new Redis();

io.on('connection', socket => {
    console.log('roulette: user connected');
});

redis.psubscribe('*', (err, count) => {});

redis.on('pmessage', (pattern, channel, message) => {
    if (channel === 'rouletteBet' || channel === 'rouletteResult') {
        io.emit(channel, message);
    }
}); 