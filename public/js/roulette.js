// public/js/roulette.js
document.addEventListener('DOMContentLoaded', function() {
    // Подключаемся к Node.js серверу по указанному URL и порту
    // Если переменная socket уже существует, переиспользуем её
    socket = io('https://magnife.ru:2083', {
        transports: ['websocket']
    });
  
    // Элементы интерфейса
    const timerEl = document.getElementById('roulette-timer');
    const resultEl = document.getElementById('roulette-result');
    const betsEl = document.getElementById('bets');
    const betForm = document.getElementById('bet-form');
  
    // Слушаем события от сервера
  
    // Начало нового раунда
    socket.on('roundStart', function(data) {
      console.log('Раунд начался:', data);
      timerEl.textContent = data.timer;
      resultEl.textContent = '';
      betsEl.innerHTML = '';
    });
  
    // Обновление таймера
    socket.on('timer', function(data) {
      timerEl.textContent = data.timer;
    });
  
    // Результат раунда
    socket.on('roundResult', function(data) {
      console.log('Результат раунда:', data);
      resultEl.textContent = "Выпало число: " + data.result;
      if(data.winners && data.winners.length > 0) {
        let winnersHtml = '<h4>Выигравшие ставки:</h4>';
        data.winners.forEach(function(w) {
          winnersHtml += `<p>Пользователь ${w.bet.userId} выиграл ${w.payout}</p>`;
        });
        betsEl.innerHTML = winnersHtml;
      }
    });
  
    // Обработка ошибок
    socket.on('errorMessage', function(data) {
      alert(data.message);
    });
  
    // Обработка формы ставок
    betForm.addEventListener('submit', function(e) {
      e.preventDefault();
      const userId = document.getElementById('userId').value;
      const betType = document.querySelector('input[name="betType"]:checked').value;
      const amount = parseFloat(document.getElementById('amount').value);
      let betData = { userId: userId, betType: betType, amount: amount };
      if(betType === 'number') {
        betData.number = parseInt(document.getElementById('bet-number').value);
      }
      if(betType === 'color') {
        betData.color = document.querySelector('input[name="color"]:checked').value;
      }
      console.log("Отправка ставки:", betData);
      socket.emit('placeBet', betData);
    });
  
    // Обработка админской команды (если форма существует)
    const overrideForm = document.getElementById('override-form');
    if(overrideForm) {
      overrideForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const overrideNumber = parseInt(document.getElementById('override-number').value);
        const adminUserId = document.getElementById('adminUserId').value;
        socket.emit('adminOverride', { userId: adminUserId, overrideNumber: overrideNumber });
      });
    }
  });
  