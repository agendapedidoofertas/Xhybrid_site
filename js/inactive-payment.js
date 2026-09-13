/**
 * Página de site suspenso: PIX + claim de pagamento.
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
  const claimBtn = document.getElementById("inactive-claim");
  const waBtn = document.getElementById("inactive-whatsapp");
  const copyBtn = document.getElementById("inactive-copy-pix");
  const pixInput = document.getElementById("inactive-pix-payload");

  function setStatus(text, kind) {
    if (!statusEl) return;
    statusEl.hidden = !text;
    statusEl.textContent = text || "";
    statusEl.className = "inactive-status" + (kind ? " inactive-status--" + kind : "");
  }

  function applyState(state) {
    const needWa = state === "need_whatsapp";
    if (claimBtn) {
      claimBtn.hidden = needWa;
      claimBtn.disabled = state === "pending_confirm";
      if (state === "pending_confirm") {
        claimBtn.textContent = "Aguardando confirmação (72h)";
      } else {
        claimBtn.textContent = "Já paguei — reativar site";
      }
    }
    if (waBtn) {
      waBtn.hidden = !ctx.whatsapp_url;
      if (needWa) {
        waBtn.classList.add("btn-primary");
        waBtn.classList.remove("btn-outline");
      }
    }
    if (needWa) {
      setStatus(
        "Reativação automática esgotada. Envie mensagem no WhatsApp — só o admin libera o site.",
        "warn",
      );
    } else if (state === "pending_confirm") {
      setStatus(
        "Site reativado provisoriamente. Confirme o pagamento no prazo de 72h ou o acesso cai de novo.",
        "ok",
      );
    }
  }

  applyState(ctx.state || "can_claim");

  const qrImg = document.getElementById("inactive-qr");
  if (qrImg && ctx.pix_qr_url) {
    qrImg.src = ctx.pix_qr_url;
  }
  if (pixInput && ctx.pix_payload) {
    pixInput.value = ctx.pix_payload;
  }
  if (waBtn && ctx.whatsapp_url) {
    waBtn.href = ctx.whatsapp_url;
    waBtn.hidden = false;
  }

  if (copyBtn && pixInput) {
    copyBtn.addEventListener("click", async () => {
      const text = pixInput.value || ctx.pix_payload || "";
      try {
        await navigator.clipboard.writeText(text);
        setStatus("Código PIX copiado.", "ok");
      } catch (_) {
        pixInput.focus();
        pixInput.select();
        setStatus("Selecione e copie o código PIX manualmente.", "warn");
      }
    });
  }

  if (claimBtn) {
    claimBtn.addEventListener("click", async () => {
      claimBtn.disabled = true;
      setStatus("Verificando…", "");
      try {
        const res = await fetch("/api/payment_claim.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            lead_id: ctx.lead_id,
            slug: ctx.slug,
            code: ctx.code,
          }),
        });
        const data = await res.json();
        if (data.ok && (data.code === "claimed" || data.code === "already_paid" || data.code === "already_pending")) {
          setStatus(data.message || "Ok", "ok");
          if (data.code === "claimed" || data.code === "already_paid") {
            window.setTimeout(() => {
              window.location.reload();
            }, 1200);
          } else {
            applyState("pending_confirm");
          }
          return;
        }
        if (data.code === "need_whatsapp") {
          applyState("need_whatsapp");
          setStatus(data.message || "Fale no WhatsApp.", "warn");
          return;
        }
        setStatus(data.message || data.error || "Não foi possível reativar.", "warn");
        claimBtn.disabled = false;
      } catch (_) {
        setStatus("Falha de rede. Tente de novo ou fale no WhatsApp.", "warn");
        claimBtn.disabled = false;
      }
    });
  }
})();
