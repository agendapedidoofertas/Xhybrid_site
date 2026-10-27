/**
 * Página de site suspenso: Pagamento via Asaas / PIX.
 */
(function () {
  const ctxEl = document.getElementById("xh-inactive-ctx");
  if (!ctxEl) return;
  let ctx;
  try {
    ctx = JSON.parse(ctxEl.textContent || "{}");
  } catch (_) {
    return;
  }

  const statusEl = document.getElementById("inactive-status");
  const checkoutBtn = document.getElementById("inactive-checkout");
  const waBtn = document.getElementById("inactive-whatsapp");
  const copyBtn = document.getElementById("inactive-copy-pix");
  const pixInput = document.getElementById("inactive-pix-payload");

  function setStatus(text, kind) {
    if (!statusEl) return;
    statusEl.hidden = !text;
    statusEl.textContent = text || "";
    statusEl.className = "inactive-status" + (kind ? " inactive-status--" + kind : "");
  }

  // Botão Pagar agora: direciona diretamente para o checkout / Asaas
  if (checkoutBtn) {
    const targetUrl = ctx.checkout_url || ('/checkout.html?lead_id=' + (ctx.lead_id || '') + '&plan=' + encodeURIComponent(ctx.plan_tier || 'basic') + '&terms=1');
    checkoutBtn.href = targetUrl;
    checkoutBtn.hidden = false;
  }

  // QR Code e Copia-e-cola do PIX
  const qrImg = document.getElementById("inactive-qr");
  if (qrImg && ctx.pix_qr_url) {
    qrImg.src = ctx.pix_qr_url;
  }
  if (pixInput && ctx.pix_payload) {
    pixInput.value = ctx.pix_payload;
  }

  // Botão WhatsApp comercial de suporte
  if (waBtn && ctx.whatsapp_url) {
    waBtn.href = ctx.whatsapp_url;
    waBtn.hidden = false;
  }

  if (copyBtn && pixInput) {
    copyBtn.addEventListener("click", async () => {
      const text = pixInput.value || ctx.pix_payload || "";
      try {
        await navigator.clipboard.writeText(text);
        setStatus("Código PIX copiado com sucesso.", "ok");
      } catch (_) {
        pixInput.focus();
        pixInput.select();
        setStatus("Selecione e copie o código PIX acima.", "warn");
      }
    });
  }
})();
