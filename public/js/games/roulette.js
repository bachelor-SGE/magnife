console.log('roulette.js loaded');

var socket = io('wss://magnife.ru:2090', { transports: ['websocket'] });

const app = Vue.createApp({
    data() {
        return {
            round: null,
            bets: [],
            myBet: { type: '', value: '', amount: 0 },
            result: null,
            history: [],
            timer: 0,
            placing: false,
        }
    },
    mounted() {
        console.log('Vue mounted');
        this.fetchState();
        socket.on('rouletteBet', msg => this.fetchState());
        socket.on('rouletteResult', msg => {
            const data = JSON.parse(msg);
            this.result = data.result;
            this.history.unshift(data);
            setTimeout(() => { this.result = null; this.fetchState(); }, 5000);
        });
        this.startTimer();
    },
    methods: {
        fetchState() {
            fetch('/roulette/state').then(r => r.json()).then(data => {
                this.round = data.round;
                this.bets = data.bets;
                this.timer = this.round ? Math.max(0, 30 - Math.floor((Date.now() - new Date(this.round.start_time).getTime())/1000)) : 0;
            });
        },
        placeBet() {
            this.placing = true;
            fetch('/roulette/bet', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.csrf_token },
                body: JSON.stringify(this.myBet)
            }).then(r => r.json()).then(data => {
                this.placing = false;
                if (data.success) this.fetchState();
                else alert(data.error);
            });
        },
        startTimer() {
            setInterval(() => {
                if (this.round) {
                    this.timer = Math.max(0, 30 - Math.floor((Date.now() - new Date(this.round.start_time).getTime())/1000));
                }
            }, 1000);
        }
    },
    template: `
    <div class='max-w-3xl mx-auto p-6 bg-gray-900 rounded-xl shadow-lg text-white'>
        <div class='text-2xl font-bold mb-4 text-center'>Онлайн Рулетка</div>
        <div class='flex flex-col items-center mb-4'>
            <div class='roulette-wheel mb-2'>
                <!-- SVG/Canvas анимация колеса и шарика -->
                <div class='w-64 h-64 bg-gradient-to-br from-gray-800 to-gray-700 rounded-full flex items-center justify-center relative'>
                    <div v-if='result !== null' class='absolute text-4xl font-bold text-yellow-400'>{{ result }}</div>
                    <div v-else class='absolute text-2xl text-gray-400'>Ставки до: {{ timer }} сек</div>
                </div>
            </div>
            <form @submit.prevent='placeBet' class='flex gap-2 mb-2'>
                <select v-model='myBet.type' class='rounded px-2 py-1 text-black'>
                    <option value='color'>Цвет</option>
                    <option value='number'>Число</option>
                    <option value='row'>Ряд</option>
                    <option value='dozen'>Дюжина</option>
                    <option value='even'>Чет</option>
                    <option value='odd'>Нечет</option>
                </select>
                <input v-model='myBet.value' placeholder='Значение (red/black/7/1/2/3...)' class='rounded px-2 py-1 text-black'>
                <input v-model.number='myBet.amount' type='number' min='1' placeholder='Сумма' class='rounded px-2 py-1 text-black'>
                <button :disabled='placing' class='bg-green-500 hover:bg-green-600 px-4 py-1 rounded text-white font-bold'>Сделать ставку</button>
            </form>
        </div>
        <div class='mb-4'>
            <div class='font-semibold mb-1'>Ставки:</div>
            <div v-for='bet in bets' class='text-sm'>
                <span class='text-gray-400'>Игрок #{{ bet.user_id }}</span>: {{ bet.type }} {{ bet.value }} — <span class='text-yellow-400'>{{ bet.amount }}</span>
            </div>
        </div>
        <div>
            <div class='font-semibold mb-1'>История:</div>
            <div v-for='h in history.slice(0, 10)' class='text-sm'>
                Выпало: <span class='text-yellow-400'>{{ h.result }}</span>
            </div>
        </div>
    </div>
    `
});
app.mount('#roulette-app'); 