/**
 * Xhybrid Scrollytelling Engine (Apple-Style Canvas WebP Scrubbing + Live Electrical Overlays)
 * Renderização a 60 FPS com interpolação suave (lerp), partículas de energia e telemetria.
 */

(function (window, document) {
  'use strict';

  class ScrollytellingEngine {
    constructor(options = {}) {
      this.container = document.querySelector(options.container || '[data-scrolly-container]');
      this.canvas = document.querySelector(options.canvas || '#scrolly-canvas');
      if (!this.container || !this.canvas) return;

      this.ctx = this.canvas.getContext('2d', { alpha: false });
      this.totalFrames = options.totalFrames || 32;
      this.framePattern = options.framePattern || 'assets/scrollytelling/eletrica/frame-%03d.webp';
      this.images = [];
      this.loadedCount = 0;
      this.targetFrame = 1;
      this.currentFrame = 1;
      this.lerpSpeed = options.lerpSpeed || 0.12;
      this.textSteps = document.querySelectorAll(options.stepsSelector || '[data-scrolly-step]');
      this.progressBar = document.querySelector(options.progressBar || '[data-scrolly-progress]');

      // Partículas e circuitos elétricos sobrepostos
      this.particles = [];
      this.initParticles(35);
      this.animTime = 0;

      this.init();
    }

    initParticles(count) {
      for (let i = 0; i < count; i++) {
        this.particles.push({
          x: Math.random(),
          y: Math.random(),
          speed: 0.001 + Math.random() * 0.003,
          size: 1 + Math.random() * 2.5,
          alpha: 0.2 + Math.random() * 0.8,
          dir: Math.random() > 0.5 ? 1 : -1,
        });
      }
    }

    formatFrameUrl(index) {
      const padded = String(index).padStart(3, '0');
      return this.framePattern.replace('%03d', padded);
    }

    init() {
      this.preloadImages();
      this.setupCanvas();
      this.bindEvents();
      this.loop();
    }

    preloadImages() {
      for (let i = 1; i <= this.totalFrames; i++) {
        const img = new Image();
        img.src = this.formatFrameUrl(i);
        img.onload = () => {
          this.loadedCount++;
          if (this.progressBar) {
            const pct = Math.round((this.loadedCount / this.totalFrames) * 100);
            this.progressBar.style.width = pct + '%';
            if (pct >= 100) {
              this.progressBar.parentElement?.classList.add('is-loaded');
            }
          }
          if (i === 1) {
            this.renderFrame(1);
          }
        };
        img.onerror = () => {
          this.loadedCount++;
        };
        this.images.push(img);
      }
    }

    setupCanvas() {
      const dpr = Math.min(window.devicePixelRatio || 1, 2);
      const rect = this.canvas.getBoundingClientRect();
      const width = rect.width || window.innerWidth;
      const height = rect.height || window.innerHeight;

      this.canvas.width = width * dpr;
      this.canvas.height = height * dpr;
      this.ctx.scale(dpr, dpr);
      this.displayWidth = width;
      this.displayHeight = height;
    }

    bindEvents() {
      window.addEventListener('resize', () => {
        this.setupCanvas();
        this.renderFrame(Math.round(this.currentFrame));
      }, { passive: true });

      window.addEventListener('scroll', () => {
        this.calculateScrollProgress();
      }, { passive: true });

      this.calculateScrollProgress();
    }

    calculateScrollProgress() {
      const rect = this.container.getBoundingClientRect();
      const windowHeight = window.innerHeight;
      const totalScrollable = rect.height - windowHeight;

      if (totalScrollable <= 0) return;

      const progress = Math.max(0, Math.min(1, -rect.top / totalScrollable));
      this.targetFrame = Math.max(1, Math.min(this.totalFrames, Math.round(progress * (this.totalFrames - 1)) + 1));
      this.scrollProgress = progress;

      this.updateNarrativeSteps(progress);
    }

    updateNarrativeSteps(progress) {
      if (!this.textSteps.length) return;

      this.textSteps.forEach((step) => {
        const start = parseFloat(step.dataset.stepStart || '0');
        const end = parseFloat(step.dataset.stepEnd || '1');
        const isActive = progress >= start && progress <= end;

        if (isActive) {
          step.classList.add('is-active');
        } else {
          step.classList.remove('is-active');
        }
      });
    }

    renderFrame(frameIndex) {
      const index = Math.max(1, Math.min(this.totalFrames, frameIndex));
      const img = this.images[index - 1];

      const cw = this.displayWidth;
      const ch = this.displayHeight;

      this.ctx.fillStyle = '#0a0c10';
      this.ctx.fillRect(0, 0, cw, ch);

      if (img && img.complete && img.naturalWidth > 0) {
        const iw = img.naturalWidth;
        const ih = img.naturalHeight;
        const scale = Math.min(cw / iw, ch / ih);
        const nw = iw * scale;
        const nh = ih * scale;
        const nx = (cw - nw) / 2;
        const ny = (ch - nh) / 2;

        this.ctx.drawImage(img, nx, ny, nw, nh);
      }

      // Efeitos de overlays elétricos de alta tecnologia (Apple-Style Telemetry)
      this.renderElectricalOverlays(cw, ch);
    }

    renderElectricalOverlays(cw, ch) {
      const p = this.scrollProgress || 0;
      this.animTime += 0.03;

      // 1. Grid sutil de coordenadas técnicas
      this.ctx.strokeStyle = 'rgba(0, 210, 255, 0.05)';
      this.ctx.lineWidth = 1;
      const gridSize = 80;
      for (let x = 0; x < cw; x += gridSize) {
        this.ctx.beginPath();
        this.ctx.moveTo(x, 0);
        this.ctx.lineTo(x, ch);
        this.ctx.stroke();
      }

      // 2. Telemetria digital flutuante no canto superior esquerdo
      const voltage = (220.0 + Math.sin(this.animTime) * 1.8).toFixed(1);
      const current = (42.0 + p * 38.0 + Math.cos(this.animTime * 1.5) * 1.2).toFixed(1);
      const freq = (59.98 + Math.sin(this.animTime * 0.7) * 0.04).toFixed(2);
      const stageName = p < 0.28 ? 'DIAGRAMA / BLUEPRINT' : p < 0.65 ? 'QUADRO QGBT / DISJUNTORES' : p < 0.85 ? 'TERMOGRAFIA NBR 5410' : 'CARGA WALLBOX ATIVA';

      this.ctx.fillStyle = 'rgba(10, 12, 16, 0.65)';
      this.ctx.strokeStyle = 'rgba(0, 210, 255, 0.25)';
      this.ctx.lineWidth = 1;
      this.ctx.beginPath();
      this.ctx.roundRect(24, 70, 260, 95, 12);
      this.ctx.fill();
      this.ctx.stroke();

      this.ctx.fillStyle = '#00d2ff';
      this.ctx.font = '600 10px monospace';
      this.ctx.fillText('⚡ TELEMETRIA EM TEMPO REAL', 38, 88);

      this.ctx.fillStyle = '#ffffff';
      this.ctx.font = '700 13px sans-serif';
      this.ctx.fillText(`${voltage} V  |  ${current} A  |  ${freq} Hz`, 38, 110);

      this.ctx.fillStyle = '#f8ab5e';
      this.ctx.font = '600 10px monospace';
      this.ctx.fillText(`MODO: ${stageName}`, 38, 130);

      this.ctx.fillStyle = '#30d158';
      this.ctx.beginPath();
      this.ctx.arc(38, 148, 4, 0, Math.PI * 2);
      this.ctx.fill();

      this.ctx.fillStyle = '#86868b';
      this.ctx.font = '500 10px sans-serif';
      this.ctx.fillText(`STATUS: NOMINAL • ART ATIVA`, 48, 151);

      // 3. Partículas de energia fluindo
      for (let i = 0; i < this.particles.length; i++) {
        const pt = this.particles[i];
        pt.y += pt.speed * (1 + p * 3);
        if (pt.y > 1) pt.y = 0;

        const px = pt.x * cw;
        const py = pt.y * ch;
        const glow = 0.3 + 0.7 * Math.sin(this.animTime * 2 + i);

        this.ctx.fillStyle = `rgba(0, 210, 255, ${pt.alpha * glow})`;
        this.ctx.beginPath();
        this.ctx.arc(px, py, pt.size, 0, Math.PI * 2);
        this.ctx.fill();
      }

      // 4. Linhas de pulso de circuito no centro
      const pulseY = ch * (0.2 + p * 0.6);
      this.ctx.strokeStyle = 'rgba(0, 210, 255, 0.3)';
      this.ctx.lineWidth = 1.5;
      this.ctx.beginPath();
      this.ctx.moveTo(cw * 0.1, pulseY);
      this.ctx.lineTo(cw * 0.3, pulseY);
      this.ctx.lineTo(cw * 0.35, pulseY - 20);
      this.ctx.lineTo(cw * 0.65, pulseY - 20);
      this.ctx.lineTo(cw * 0.7, pulseY);
      this.ctx.lineTo(cw * 0.9, pulseY);
      this.ctx.stroke();

      // Ponto de faísca pulsante
      const sparkX = cw * (0.1 + (Math.sin(this.animTime * 2) * 0.5 + 0.5) * 0.8);
      this.ctx.fillStyle = '#ffffff';
      this.ctx.shadowColor = '#00d2ff';
      this.ctx.shadowBlur = 12;
      this.ctx.beginPath();
      this.ctx.arc(sparkX, pulseY, 3, 0, Math.PI * 2);
      this.ctx.fill();
      this.ctx.shadowBlur = 0;
    }

    loop() {
      const diff = this.targetFrame - this.currentFrame;
      if (Math.abs(diff) > 0.005) {
        this.currentFrame += diff * this.lerpSpeed;
      }
      this.renderFrame(Math.round(this.currentFrame));

      requestAnimationFrame(() => this.loop());
    }
  }

  window.ScrollytellingEngine = ScrollytellingEngine;

  document.addEventListener('DOMContentLoaded', () => {
    if (document.querySelector('[data-scrolly-container]')) {
      new ScrollytellingEngine({ totalFrames: 32 });
    }
  });
})(window, document);
