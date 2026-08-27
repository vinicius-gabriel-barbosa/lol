// public/js/charts.js - Canvas Stats Renderer for Recall LoL

window.RecallCharts = {
    // 1. Renderiza Gráfico de Barras Horizontal ou Vertical
    renderBarChart: function(canvasId, labels, values, colors) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        const width = canvas.width = canvas.parentElement.clientWidth || 400;
        const height = canvas.height = 240;

        ctx.clearRect(0, 0, width, height);

        const maxValue = Math.max(...values, 1);
        const padding = 40;
        const chartWidth = width - padding * 2;
        const chartHeight = height - padding * 2;
        const barWidth = chartWidth / values.length - 15;

        values.forEach((val, index) => {
            const barHeight = (val / maxValue) * chartHeight;
            const x = padding + index * (barWidth + 15);
            const y = height - padding - barHeight;

            // Gradient Fill
            const gradient = ctx.createLinearGradient(0, y, 0, height - padding);
            const color = colors[index % colors.length] || '#0ac8b9';
            gradient.addColorStop(0, color);
            gradient.addColorStop(1, 'rgba(10, 200, 185, 0.2)');

            ctx.fillStyle = gradient;
            ctx.beginPath();
            ctx.roundRect(x, y, barWidth, barHeight, [6, 6, 0, 0]);
            ctx.fill();

            // Label Text
            ctx.fillStyle = '#8b9bb4';
            ctx.font = '12px Inter, sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText(labels[index], x + barWidth / 2, height - 15);

            // Value Text
            ctx.fillStyle = '#ffffff';
            ctx.font = 'bold 12px Inter, sans-serif';
            ctx.fillText(val, x + barWidth / 2, y - 8);
        });
    },

    // 2. Renderiza Gráfico Donut de Win Rate / Vitórias
    renderDonutChart: function(canvasId, winRatePct, titleText) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        const width = canvas.width = canvas.parentElement.clientWidth || 220;
        const height = canvas.height = 220;

        const centerX = width / 2;
        const centerY = height / 2;
        const radius = Math.min(width, height) / 2 - 20;

        ctx.clearRect(0, 0, width, height);

        // Fundo do círculo
        ctx.beginPath();
        ctx.arc(centerX, centerY, radius, 0, 2 * Math.PI);
        ctx.strokeStyle = 'rgba(255, 255, 255, 0.1)';
        ctx.lineWidth = 16;
        ctx.stroke();

        // Arco da taxa de vitória
        const angle = (winRatePct / 100) * 2 * Math.PI;
        ctx.beginPath();
        ctx.arc(centerX, centerY, radius, -Math.PI / 2, angle - Math.PI / 2);
        ctx.strokeStyle = '#0ac8b9';
        ctx.lineWidth = 16;
        ctx.lineCap = 'round';
        ctx.stroke();

        // Texto central
        ctx.fillStyle = '#ffffff';
        ctx.font = 'bold 24px Outfit, sans-serif';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(winRatePct + '%', centerX, centerY - 6);

        ctx.fillStyle = '#8b9bb4';
        ctx.font = '11px Inter, sans-serif';
        ctx.fillText(titleText || 'Win Rate', centerX, centerY + 18);
    }
};
