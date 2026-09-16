# Portão de segurança — antes de escalar Medium/Pro

Checklist de regressão (marcar no staging e de novo em prod).

## Auth
- [ ] `/admin/` sem login → redirect login
- [ ] Cliente Medium/Pro só vê o próprio lead
- [ ] Setup bloqueado após 1º user
- [ ] Cookie Secure/HttpOnly/SameSite em HTTPS
- [ ] CSRF em POSTs admin

## Dados
- [ ] `/data/*.sqlite` → 403
- [ ] `pix_config.php`, `crm_bridge.php`, `payment_gateway.php` não baixáveis
- [ ] Upload sem execução PHP

## Pagamento
- [ ] `PAYMENT_MODE=manual`: claim rate-limited; grace 72h
- [ ] `stub`: webhook/stub ativa/desativa lead
- [ ] `live`: webhook rejeita sem token; eventos idempotentes
- [ ] Claim manual bloqueado fora de `manual`

## Hosts
- [ ] Basic resolve em `{slug}.8xd.com.br` quando `public_host` gravado
- [ ] Path legado ainda funciona
- [ ] Medium/Pro com domínio verificado serve por `HTTP_HOST`

## Planos
- [ ] Medium: cliente sem Marca
- [ ] Pro: Marca liberada
- [ ] Inactive + reativação conforme mode

## Infra
- [ ] Só HTTPS
- [ ] Backup + restore testado 1×
- [ ] Cron grace no ar
