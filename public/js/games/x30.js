window.newGameCalls = (window.newGameCalls || 0) + 1;
console.log("🧠 newGame() вызвана — уже", window.newGameCalls, "раз(а)");

var gameTimer = null;

socket = io('https://magnife.ru:2083', {
    transports: ['websocket']
});



// Получение случайного числа
function getRandomInt(min, max) {
    return Math.floor(Math.random() * (max - min + 1)) + min;
}

function newGame () {
    console.log("🟢 newGame() вызвана");

    if (gameTimer) {
        clearInterval(gameTimer);
        gameTimer = null;
    }

    let time = 30;

    gameTimer = setInterval(() => {
        time--;
        if (time < 0) return; // 🛡️ защита от отрицательных значений

        console.log("⏳ newGame таймер:", time);

        if (time <= 0) {
            clearInterval(gameTimer);
            gameTimer = null;
            console.log("⌛ Ожидание события WHEEL_TIME от сервера");
        }

        $('#x30__timer').text(time);
    }, 1000);

    $('#x30__text').text('Начало через');
}


// Стартовая инициализация после загрузки документа
$(document).ready(function() {
    console.log("📦 $(document).ready → вызываем newGame()");
    newGame();
});
