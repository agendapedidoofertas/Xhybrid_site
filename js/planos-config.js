/**
 * Preços e copy dos planos (fallback se /api/planos_offer.php falhar).
 */
window.XH_PLANOS = {
  intro: 'Site pronto, WhatsApp e manutenção no pacote. Escolha o plano e como pagar.',
  eyebrow: 'Oferta',
  title: 'Planos',
  highlight: 'pleno',
  referral_note:
    'Indicação: R$ 50 por indicação aceita, até R$ 150. Preços da oferta vigente; impostos/taxas conforme a contratação.',
  terms_summary:
    'Ao contratar, pagar ou usar os serviços Xhybrid / 8xd, você concorda com estes Termos e com a Política de Privacidade (LGPD).\n\nPlanos: Basic (subdomínio), Pleno (domínio próprio, personalização limitada) e Plus (personalização ampliada).\n\nIA: revise dados comerciais antes de publicar.\n\nPagamento: Asaas e/ou Stripe. Inadimplência pode suspender o site.\n\nIndicação: R$ 50 por indicação paga elegível, até R$ 150.',
  terms_url: '/termos.html',
  privacy_summary:
    'A Xhybrid / 8xd trata dados para contratar e operar os planos Basic, Pleno e Plus (site, painel, CRM e cobrança).\n\nColetamos dados de checkout (empresa, e-mail, WhatsApp), plano, provedor (Asaas/Stripe) e conteúdo que você edita no painel.\n\nBases principais: execução de contrato e legítimo interesse operacional. Não vendemos listas.\n\nPagamentos são processados por Asaas e/ou Stripe. Dados ficam em servidor (pastas data/ não públicas).\n\nPara acesso, correção ou exclusão (LGPD), fale pelos canais WhatsApp/e-mail da agência.',
  privacy_url: '/privacidade.html',
  plans: {
    basic: {
      id: 'basic',
      label: 'Basic',
      planCents: 49700,
      maintenanceCents: 5990,
      bullets: [
        'Subdomínio *.8xd.com.br',
        'Site completo com personalização mínima',
        'Geração assistida por IA (você revisa)',
        'WhatsApp + hospedagem/SSL no pacote',
      ],
    },
    pleno: {
      id: 'pleno',
      label: 'Pleno',
      planCents: 69700,
      maintenanceCents: 7999,
      bullets: [
        'Domínio próprio (taxa de registro à parte, se houver)',
        'Personalização limitada',
        'Preset de nicho e animações',
        'Tudo do Basic + mais suporte',
      ],
    },
    plus: {
      id: 'plus',
      label: 'Plus',
      planCents: 99700,
      maintenanceCents: 7999,
      bullets: [
        'Domínio próprio',
        'Personalização total · looks premium',
        'Marca editável pelo cliente',
        'FAQ, urgência e tetos amplos',
      ],
    },
  },
  formatBRL(cents) {
    return (cents / 100).toLocaleString('pt-BR', {
      style: 'currency',
      currency: 'BRL',
    });
  },
};
