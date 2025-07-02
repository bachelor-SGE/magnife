// Rock Climber Slot Game
class RockClimberSlot {
    constructor(container) {
        this.container = container;
        this.app = null;
        this.reels = [];
        this.symbols = ['🧗', '⛰️', '🏔️', '🔨', '⛏️', '💎', '🏆', '🌟'];
        this.isSpinning = false;
        this.balance = 1000;
        this.bet = 10;
        this.winLines = [
            [0, 1, 2, 3, 4], // горизонтальная линия
            [0, 1, 2, 3, 4], // вторая линия
            [0, 1, 2, 3, 4]  // третья линия
        ];
        
        this.init();
    }
    
    init() {
        this.app = new PIXI.Application({
            width: 800,
            height: 600,
            backgroundColor: 0x1b2030,
            antialias: true
        });
        
        this.container.innerHTML = '';
        this.container.appendChild(this.app.view);
        
        this.createBackground();
        this.createUI();
        this.createReels();
        this.createPaytable();
    }
    
    createBackground() {
        const background = new PIXI.Graphics();
        background.beginFill(0x20273a);
        background.drawRect(0, 0, 800, 600);
        background.endFill();
        
        // Добавляем горные узоры
        const mountains = new PIXI.Graphics();
        mountains.beginFill(0x6080b0, 0.3);
        mountains.moveTo(0, 600);
        mountains.lineTo(200, 400);
        mountains.lineTo(400, 500);
        mountains.lineTo(600, 350);
        mountains.lineTo(800, 450);
        mountains.lineTo(800, 600);
        mountains.endFill();
        
        this.app.stage.addChild(background);
        this.app.stage.addChild(mountains);
    }
    
    createUI() {
        const ui = new PIXI.Container();
        
        // Кнопка спина
        const spinButton = new PIXI.Graphics();
        spinButton.beginFill(0x6080b0);
        spinButton.drawRoundedRect(0, 0, 120, 40, 8);
        spinButton.endFill();
        spinButton.x = 350;
        spinButton.y = 520;
        
        const spinText = new PIXI.Text('SPIN', {
            fontFamily: 'Arial',
            fontSize: 16,
            fill: 0xffffff,
            fontWeight: 'bold'
        });
        spinText.x = 380;
        spinText.y = 530;
        
        spinButton.interactive = true;
        spinButton.buttonMode = true;
        spinButton.on('pointerdown', () => this.spin());
        
        ui.addChild(spinButton);
        ui.addChild(spinText);
        
        // Баланс
        const balanceText = new PIXI.Text(`Balance: $${this.balance}`, {
            fontFamily: 'Arial',
            fontSize: 18,
            fill: 0xffffff
        });
        balanceText.x = 20;
        balanceText.y = 20;
        ui.addChild(balanceText);
        
        // Ставка
        const betText = new PIXI.Text(`Bet: $${this.bet}`, {
            fontFamily: 'Arial',
            fontSize: 18,
            fill: 0xffffff
        });
        betText.x = 20;
        betText.y = 50;
        ui.addChild(betText);
        
        // Название игры
        const titleText = new PIXI.Text('Rock Climber', {
            fontFamily: 'Arial',
            fontSize: 24,
            fill: 0x6080b0,
            fontWeight: 'bold'
        });
        titleText.x = 300;
        titleText.y = 20;
        ui.addChild(titleText);
        
        this.app.stage.addChild(ui);
    }
    
    createReels() {
        const reelWidth = 120;
        const reelHeight = 300;
        const startX = 200;
        const startY = 150;
        
        for (let i = 0; i < 5; i++) {
            const reel = new PIXI.Container();
            reel.x = startX + i * reelWidth;
            reel.y = startY;
            
            // Создаем символы для каждого барабана
            for (let j = 0; j < 3; j++) {
                const symbolContainer = new PIXI.Container();
                
                const symbolBg = new PIXI.Graphics();
                symbolBg.beginFill(0x283046);
                symbolBg.drawRoundedRect(0, 0, reelWidth - 10, reelHeight / 3 - 10, 8);
                symbolBg.endFill();
                symbolBg.y = j * (reelHeight / 3);
                
                const symbolText = new PIXI.Text(this.symbols[Math.floor(Math.random() * this.symbols.length)], {
                    fontFamily: 'Arial',
                    fontSize: 32,
                    fill: 0x6080b0
                });
                symbolText.x = (reelWidth - 10) / 2 - 16;
                symbolText.y = j * (reelHeight / 3) + (reelHeight / 3) / 2 - 16;
                
                symbolContainer.addChild(symbolBg);
                symbolContainer.addChild(symbolText);
                reel.addChild(symbolContainer);
            }
            
            this.reels.push(reel);
            this.app.stage.addChild(reel);
        }
    }
    
    createPaytable() {
        const paytable = new PIXI.Container();
        paytable.x = 600;
        paytable.y = 150;
        
        const paytableBg = new PIXI.Graphics();
        paytableBg.beginFill(0x283046);
        paytableBg.drawRoundedRect(0, 0, 180, 300, 8);
        paytableBg.endFill();
        paytable.addChild(paytableBg);
        
        const paytableTitle = new PIXI.Text('Paytable', {
            fontFamily: 'Arial',
            fontSize: 16,
            fill: 0x6080b0,
            fontWeight: 'bold'
        });
        paytableTitle.x = 60;
        paytableTitle.y = 10;
        paytable.addChild(paytableTitle);
        
        let yPos = 40;
        this.symbols.forEach((symbol, index) => {
            const symbolText = new PIXI.Text(symbol, {
                fontFamily: 'Arial',
                fontSize: 20,
                fill: 0x6080b0
            });
            symbolText.x = 10;
            symbolText.y = yPos;
            
            const payoutText = new PIXI.Text(`$${(index + 1) * 10}`, {
                fontFamily: 'Arial',
                fontSize: 14,
                fill: 0xffffff
            });
            payoutText.x = 140;
            payoutText.y = yPos + 3;
            
            paytable.addChild(symbolText);
            paytable.addChild(payoutText);
            yPos += 30;
        });
        
        this.app.stage.addChild(paytable);
    }
    
    spin() {
        if (this.isSpinning) return;
        
        if (this.balance < this.bet) {
            alert('Недостаточно средств!');
            return;
        }
        
        this.balance -= this.bet;
        this.isSpinning = true;
        
        // Обновляем UI
        this.app.stage.children[1].children[2].text = `Balance: $${this.balance}`;
        
        // Анимация вращения с эффектом "восхождения"
        const promises = this.reels.map((reel, index) => {
            return new Promise(resolve => {
                const duration = 2 + index * 0.2;
                gsap.to(reel, {
                    y: reel.y - 300, // Восхождение вверх
                    duration: duration,
                    ease: "power2.out",
                    onComplete: () => {
                        reel.y = 150;
                        this.randomizeReel(reel);
                        resolve();
                    }
                });
            });
        });
        
        Promise.all(promises).then(() => {
            this.isSpinning = false;
            this.checkWin();
        });
    }
    
    randomizeReel(reel) {
        reel.children.forEach((symbolContainer, index) => {
            const symbolText = symbolContainer.children[1];
            symbolText.text = this.symbols[Math.floor(Math.random() * this.symbols.length)];
        });
    }
    
    checkWin() {
        let totalWin = 0;
        
        this.winLines.forEach((line, lineIndex) => {
            const lineSymbols = line.map((reelIndex, symbolIndex) => {
                const reel = this.reels[reelIndex];
                const symbolContainer = reel.children[symbolIndex];
                return symbolContainer.children[1].text;
            });
            
            // Проверяем комбинации
            const firstSymbol = lineSymbols[0];
            let consecutiveCount = 1;
            
            for (let i = 1; i < lineSymbols.length; i++) {
                if (lineSymbols[i] === firstSymbol) {
                    consecutiveCount++;
                } else {
                    break;
                }
            }
            
            if (consecutiveCount >= 3) {
                const symbolIndex = this.symbols.indexOf(firstSymbol);
                const winAmount = (symbolIndex + 1) * 10 * consecutiveCount;
                totalWin += winAmount;
                
                // Подсвечиваем выигрышную линию
                this.highlightWinLine(line, lineIndex);
            }
        });
        
        if (totalWin > 0) {
            this.balance += totalWin;
            this.app.stage.children[1].children[2].text = `Balance: $${this.balance}`;
            alert(`Поздравляем! Выигрыш: $${totalWin}`);
        }
    }
    
    highlightWinLine(line, lineIndex) {
        const highlight = new PIXI.Graphics();
        highlight.lineStyle(4, 0x6080b0, 0.8);
        
        line.forEach((reelIndex, symbolIndex) => {
            const reel = this.reels[reelIndex];
            const x = reel.x + 60;
            const y = reel.y + symbolIndex * 100 + 50;
            
            if (symbolIndex === 0) {
                highlight.moveTo(x, y);
            } else {
                highlight.lineTo(x, y);
            }
        });
        
        this.app.stage.addChild(highlight);
        
        // Убираем подсветку через 2 секунды
        setTimeout(() => {
            this.app.stage.removeChild(highlight);
        }, 2000);
    }
}

// Инициализация игры
if (typeof window !== 'undefined') {
    window.RockClimberSlot = RockClimberSlot;
} 